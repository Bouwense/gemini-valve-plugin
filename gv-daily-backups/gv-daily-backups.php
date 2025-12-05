<?php
/**
 * Plugin Name: GV – Daily Backups (Files + DB, ZIP, Retentie)
 * Description: Maakt dagelijks een ZIP-backup met DB-dump en bestanden. Retentie: 1 week, behalve maandag (1 maand). Inclusief handmatige run via Tools.
 * Author: Gemini Valve
 * Version: 1.0.0
 */

if ( ! defined('ABSPATH') ) exit;

class GV_Daily_Backups {
    const CRON_HOOK   = 'gv_daily_backups_run';
    const OPTION_LOG  = 'gv_backups_last_log';
    const NONCE       = 'gv_backup_now';
    const PAGE_SLUG   = 'gv-daily-backups';

    /** ===== Settings (filters beschikbaar) ===== */
    public static function backup_dir() {
        $default = wp_normalize_path( WP_CONTENT_DIR . '/uploads/gv-backups' );
        $dir = apply_filters('gv_backup_dir', $default);
        return rtrim( wp_normalize_path( $dir ), '/' );
    }
    public static function root_paths() {
        // Welke paden wil je in ZIP (standaard: hele site root)
        $paths = [ ABSPATH ];
        return apply_filters('gv_backup_paths', array_map('wp_normalize_path', $paths));
    }
    public static function exclude_paths() {
        // Typische caches / eigen backupmap uitsluiten
        $ex = [
            self::backup_dir(),
            WP_CONTENT_DIR . '/cache',
            WP_CONTENT_DIR . '/wflogs',
            WP_CONTENT_DIR . '/uploads/cache',
            WP_CONTENT_DIR . '/ai1wm-backups',
            WP_CONTENT_DIR . '/updraft',
            WP_CONTENT_DIR . '/upgrade',
            ABSPATH . 'node_modules',
            ABSPATH . 'vendor',
        ];
        return apply_filters('gv_backup_excludes', array_map('wp_normalize_path', $ex));
    }

    /** ===== Bootstrap ===== */
    public static function init() {
        add_action('admin_menu', [__CLASS__,'admin_menu']);
        add_action('admin_post_gv_backup_now', [__CLASS__, 'handle_manual_backup']);
        add_action(self::CRON_HOOK, [__CLASS__, 'run_backup']);
        register_activation_hook(__FILE__, [__CLASS__, 'activate']);
        register_deactivation_hook(__FILE__, [__CLASS__, 'deactivate']);
    }

    public static function activate() {
        // Maak map + protect files
        self::ensure_backup_dir();

        if ( ! wp_next_scheduled(self::CRON_HOOK) ) {
            // Plan eerste run ~03:10 server-tijd
            $first = strtotime('tomorrow 03:10');
            if ( ! $first ) { $first = time() + 6 * HOUR_IN_SECONDS; }
            wp_schedule_event( $first, 'daily', self::CRON_HOOK );
        }
    }

    public static function deactivate() {
        $ts = wp_next_scheduled(self::CRON_HOOK);
        if ( $ts ) wp_unschedule_event($ts, self::CRON_HOOK);
    }

    /** ===== Admin UI ===== */
    public static function admin_menu() {
        add_management_page(
            'GV Backups', 'GV Backups', 'manage_options', self::PAGE_SLUG, [__CLASS__, 'render_admin_page']
        );
    }

    public static function render_admin_page() {
        if ( ! current_user_can('manage_options') ) wp_die('Insufficient permissions');

        $dir   = self::backup_dir();
        $files = self::list_backups();

        $log   = get_option(self::OPTION_LOG, '');
        ?>
        <div class="wrap">
            <h1>GV Backups</h1>
            <p>Backups worden opgeslagen in: <code><?php echo esc_html($dir); ?></code></p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field(self::NONCE); ?>
                <input type="hidden" name="action" value="gv_backup_now">
                <p>
                    <button class="button button-primary">Nu back-uppen</button>
                </p>
            </form>

            <h2>Laatst log</h2>
            <pre style="max-height:240px;overflow:auto;background:#111;color:#0f0;padding:12px;border-radius:6px;"><?php echo esc_html($log); ?></pre>

            <h2>Backups</h2>
            <?php if (empty($files)): ?>
                <p>Er zijn nog geen backups.</p>
            <?php else: ?>
                <table class="widefat striped">
                    <thead><tr><th>Bestand</th><th>Grootte</th><th>Datum</th></tr></thead>
                    <tbody>
                    <?php foreach ($files as $f): ?>
                        <tr>
                            <td><a href="<?php echo esc_url( self::file_download_url($f['path']) ); ?>"><?php echo esc_html(basename($f['path'])); ?></a></td>
                            <td><?php echo esc_html( size_format($f['size']) ); ?></td>
                            <td><?php echo esc_html( date_i18n( 'Y-m-d H:i', $f['mtime'] ) ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    public static function handle_manual_backup() {
        if ( ! current_user_can('manage_options') ) wp_die('Insufficient permissions');
        check_admin_referer(self::NONCE);
        self::run_backup(true);
        wp_safe_redirect( admin_url('tools.php?page=' . self::PAGE_SLUG) );
        exit;
    }

    /** ===== Core backup flow ===== */
    public static function run_backup($manual = false) {
        self::ensure_backup_dir();
        $log  = [];
        $push = function($m) use (&$log){ $log[] = '['.date('Y-m-d H:i:s')."] ".$m; };

        try {
            $dir = self::backup_dir();

            // Bestandsnaam met weekday label (Mon/Tue/…)
            $ts       = current_time('timestamp');
            $weekday  = strtoupper( date_i18n('D', $ts) ); // MON/TUE/...
            $filename = sprintf('backup-%s-%s.zip', date_i18n('Ymd-His', $ts), $weekday);
            $zipPath  = wp_normalize_path($dir . '/' . $filename);

            $push("Start backup → $filename");

            // 1) Genereer DB dump (.sql)
            $tmpSql = self::export_database($push);

            // 2) Zip-bestand openen
            if ( ! class_exists('ZipArchive') ) {
                throw new RuntimeException('ZipArchive is niet beschikbaar in PHP (ext/zip vereist).');
            }
            $zip = new ZipArchive();
            if ( true !== $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) ) {
                throw new RuntimeException('Kon ZIP niet openen voor schrijven: ' . $zipPath);
            }

            // 2a) Voeg database dump toe
            $zip->addFile($tmpSql, 'database.sql');

            // 2b) Voeg paden toe (met uitsluitingen)
            $paths   = self::root_paths();
            $exclude = self::exclude_paths();

            foreach ($paths as $p) {
                self::zip_add_path($zip, $p, $exclude, $push);
            }

            $zip->close();
            @unlink($tmpSql);

            $size = file_exists($zipPath) ? filesize($zipPath) : 0;
            $push('ZIP klaar: ' . basename($zipPath) . ' (' . size_format($size) . ')');

            // 3) Retentie toepassen
            self::apply_retention($push);

            $push('Backup afgerond.');
        } catch (Throwable $e) {
            $push('FOUT: ' . $e->getMessage());
        }

        update_option(self::OPTION_LOG, implode("\n", $log), false);
    }

    /** ===== Database export ===== */
    protected static function export_database($push_cb) {
        $push = $push_cb ?: function(){};
        $tmp  = wp_normalize_path( wp_tempnam('gvdb') );
        if ( ! $tmp ) { $tmp = wp_normalize_path( self::backup_dir() . '/tmp-db-' . uniqid() . '.sql' ); }

        // 1) Probeer mysqldump
        $cmd = self::build_mysqldump_command($tmp);
        if ( $cmd ) {
            $push('Probeer mysqldump…');
            @exec($cmd, $out, $code);
            if ($code === 0 && filesize($tmp) > 0) {
                $push('mysqldump gelukt.');
                return $tmp;
            }
            $push('mysqldump niet beschikbaar of mislukt (val terug op PHP-dump).');
            @unlink($tmp);
        }

        // 2) PHP fallback (universeel, langzamer, maar werkt zonder shell)
        $push('Start PHP database export…');
        global $wpdb;

        $fh = fopen($tmp, 'wb');
        if ( ! $fh ) throw new RuntimeException('Kon tijdelijk SQL-bestand niet openen: ' . $tmp);

        $db = DB_NAME;
        fwrite($fh, "-- GV Backup SQL dump\n-- DB: {$db}\n-- " . date('Y-m-d H:i:s') . "\n\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");

        $tables = $wpdb->get_col('SHOW TABLES');
        foreach ($tables as $table) {
            // CREATE
            $create = $wpdb->get_row("SHOW CREATE TABLE `$table`", ARRAY_N);
            if ( $create && ! empty($create[1]) ) {
                fwrite($fh, "DROP TABLE IF EXISTS `$table`;\n" . $create[1] . ";\n\n");
            }

            // DATA
            $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM `$table`");
            if ( $count > 0 ) {
                $cols = $wpdb->get_results("SHOW COLUMNS FROM `$table`", ARRAY_A);
                $colNames = array_map(function($c){ return '`'.$c['Field'].'`'; }, $cols);
                $colList  = '(' . implode(',', $colNames) . ')';

                $offset = 0; $limit = 1000;
                while ($offset < $count) {
                    $rows = $wpdb->get_results( $wpdb->prepare("SELECT * FROM `$table` LIMIT %d OFFSET %d", $limit, $offset), ARRAY_A );
                    if ( empty($rows) ) break;

                    $values = [];
                    foreach ($rows as $r) {
                        $vals = array_map(function($v) use ($wpdb){
                            if (is_null($v)) return 'NULL';
                            return "'" . esc_sql( self::sql_escape_binary( $v ) ) . "'";
                        }, array_values($r));
                        $values[] = '(' . implode(',', $vals) . ')';
                    }
                    $chunks = implode(",\n", $values);
                    fwrite($fh, "INSERT INTO `$table` $colList VALUES\n$chunks;\n\n");

                    $offset += $limit;
                }
            }
            fwrite($fh, "\n");
        }
        fwrite($fh, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($fh);

        $push('PHP database export klaar.');
        return $tmp;
    }

    protected static function sql_escape_binary($v) {
        // Minimal escape; wpdb->prepare al gebruikt voor SELECTS. Voor dump: backslash quotes & backslashes
        return str_replace(["\\", "'"], ["\\\\", "\\'"], (string)$v);
    }

    protected static function build_mysqldump_command($outfile) {
        // Alleen als shell_exec/exec toegestaan is
        if ( ! function_exists('exec') ) return null;

        $host = DB_HOST;
        $port = '';
        // DB_HOST kan "host:port" zijn
        if (strpos($host, ':') !== false) {
            list($hostOnly, $portPart) = explode(':', $host, 2);
            $host = $hostOnly;
            if (ctype_digit($portPart)) $port = " --port=" . escapeshellarg($portPart);
        }

        $binCandidates = ['mysqldump', '/usr/bin/mysqldump', '/usr/local/bin/mysqldump', 'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe'];
        $bin = null;
        foreach ($binCandidates as $c) {
            $check = escapeshellcmd($c) . ' --version';
            @exec($check, $o, $code);
            if ($code === 0) { $bin = $c; break; }
        }
        if ( ! $bin ) return null;

        $cmd = sprintf(
            '%s --single-transaction --quick --default-character-set=utf8mb4 -h %s -u %s -p%s %s > %s',
            escapeshellcmd($bin),
            escapeshellarg($host),
            escapeshellarg(DB_USER),
            escapeshellarg(DB_PASSWORD),
            escapeshellarg(DB_NAME) . $port,
            escapeshellarg($outfile)
        );

        return $cmd;
    }

    /** ===== Files → ZIP ===== */
    protected static function zip_add_path(ZipArchive $zip, $path, array $exclude, $push) {
        $path = wp_normalize_path($path);
        if ( ! file_exists($path) ) return;

        $isExcluded = function($p) use ($exclude) {
            $p = wp_normalize_path($p);
            foreach ($exclude as $ex) {
                $ex = rtrim(wp_normalize_path($ex), '/');
                if ( strpos($p, $ex) === 0 ) return true;
            }
            return false;
        };

        $baseLen = strlen( rtrim($path, '/') ) + 1;

        if ( is_file($path) ) {
            if ( ! $isExcluded($path) ) {
                $local = ltrim( substr($path, $baseLen-1), '/' );
                $zip->addFile($path, 'files/' . $local);
            }
            return;
        }

        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($it as $fs) {
            $p = wp_normalize_path($fs->getPathname());
            if ( $isExcluded($p) ) continue;

            if ( $fs->isDir() ) {
                // mappen worden impliciet aangemaakt door addFile; overslaan oké
                continue;
            }
            $local = ltrim( substr($p, $baseLen), '/' );
            $zip->addFile($p, 'files/' . $local);
        }

        $push('Toegevoegd: ' . basename($path));
    }

    /** ===== Retentie: 7 dagen, behalve maandag (D=Mon) → 30 dagen ===== */
    protected static function apply_retention($push) {
        $files = self::list_backups();
        $now   = current_time('timestamp');

        foreach ($files as $f) {
            $ageDays = floor( ($now - $f['mtime']) / DAY_IN_SECONDS );

            // Bepaal weekday op basis van bestandsnaam (…-MON.zip) of mtime fallback
            $weekday = null;
            if ( preg_match('/-(MON|TUE|WED|THU|FRI|SAT|SUN)\.zip$/i', $f['name'], $m) ) {
                $weekday = strtoupper($m[1]);
            } else {
                $weekday = strtoupper( date_i18n('D', $f['mtime']) );
            }

            $keepDays = ($weekday === 'MON') ? 30 : 7;

            if ( $ageDays > $keepDays ) {
                @unlink($f['path']);
                $push('Verwijderd door retentie: ' . $f['name']);
            }
        }
    }

    /** ===== Helpers ===== */
    protected static function ensure_backup_dir() {
        $dir = self::backup_dir();
        if ( ! file_exists($dir) ) {
            wp_mkdir_p($dir);
        }
        // index.php
        $index = $dir . '/index.php';
        if ( ! file_exists($index) ) {
            file_put_contents($index, "<?php // Silence is golden.\n");
        }
        // .htaccess (Apache) – voorkom directe download via web
        $hta = $dir . '/.htaccess';
        if ( ! file_exists($hta) ) {
            file_put_contents($hta, "Options -Indexes\nDeny from all\n");
        }
    }

    protected static function list_backups() {
        $dir = self::backup_dir();
        if ( ! file_exists($dir) ) return [];
        $files = glob($dir . '/*.zip');
        if ( ! $files ) return [];
        $out = [];
        foreach ($files as $p) {
            $out[] = [
                'path'  => wp_normalize_path($p),
                'name'  => basename($p),
                'mtime' => filemtime($p),
                'size'  => filesize($p),
            ];
        }
        usort($out, function($a,$b){ return $b['mtime'] <=> $a['mtime']; });
        return $out;
    }

    protected static function file_download_url($absPath) {
        // Probeer via uploads-URL als die binnen uploads valt
        $uploads = wp_get_upload_dir();
        $baseDir = wp_normalize_path($uploads['basedir']);
        $baseUrl = $uploads['baseurl'];

        $absPath = wp_normalize_path($absPath);
        if ( strpos($absPath, $baseDir) === 0 ) {
            $rel = ltrim( substr($absPath, strlen($baseDir)), '/' );
            return $baseUrl . '/' . str_replace('\\','/',$rel);
        }
        // Fallback: geen directe URL (map is al met .htaccess afgeschermd)
        return '#';
    }
}

GV_Daily_Backups::init();
