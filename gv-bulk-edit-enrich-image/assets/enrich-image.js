// Snippet-safe JS for Enrich Image (admin)
(function () {
    function ready(fn) { if (document.readyState !== 'loading') { fn(); } else { document.addEventListener('DOMContentLoaded', fn); } }

    ready(function () {
        if (typeof jQuery === 'undefined') { console.error('[GV EI] jQuery missing'); return; }
        var $ = jQuery;

        // Only run on our page
        var params = new URLSearchParams(window.location.search || '');
        if (params.get('page') !== 'gv-bulk-edit-enrich-image') return;

        console.log('[GV EI] JS snippet loaded');

        var $wrap = $('.gv-ei-wrap');
        if (!$wrap.length) { console.warn('[GV EI] .gv-ei-wrap not found'); return; }

        // WordPress admin defines `ajaxurl` globally
        var AJAX = (typeof ajaxurl !== 'undefined') ? ajaxurl : '/wp-admin/admin-ajax.php';

        // Nonce: from hidden input or data-nonce
        function getNonce() {
            var n1 = $wrap.find('[name="gv_ei_field"]').val();
            if (n1) return n1;
            var n2 = $('#gv-ei-bulk-form').data('nonce');
            if (n2) return n2;
            console.warn('[GV EI] Nonce not found; AJAX will fail with -1');
            return '';
        }

        function rowSetLoading($tr, on) { $tr.find('.spinner')[on ? 'addClass' : 'removeClass']('is-active'); }
        function renderSuggestion($tr, sug) {
            var $cell = $tr.find('.gv-ei-suggestion');
            if (!sug) {
                $cell.html('<em>No suggestion found</em>');
                $tr.find('.gv-ei-import').prop('disabled', true).data('payload', null);
                return;
            }
            var src = sug.source ? ' <small>(' + sug.source + ')</small>' : '';
            var img = sug.thumb ? '<img src="' + sug.thumb + '" alt="" style="max-width:64px;height:auto;vertical-align:middle;margin-right:8px;">' : '';
            var title = sug.title ? '<div>' + $('<div>').text(sug.title).html() + '</div>' : '';
            $cell.html(img + title + src);
            $tr.find('.gv-ei-import').prop('disabled', false).data('payload', sug);
        }

        // Never submit forms on this page
        $(document).on('submit', '.gv-ei-wrap form', function (e) { e.preventDefault(); return false; });

        // Select all
        $(document).on('change', '.gv-ei-check-all', function () {
            var on = $(this).is(':checked');
            $('.gv-ei-row-check').prop('checked', on);
        });

        // Row: Find image
        $(document).on('click', '.gv-ei-scan', function (e) {
            e.preventDefault();
            var $tr = $(this).closest('tr');
            var id = $tr.data('id');
            if (!id) { console.warn('[GV EI] Missing row data-id'); return; }

            rowSetLoading($tr, true);
            $.ajax({
                url: AJAX, method: 'POST', dataType: 'json',
                data: { action: 'gv_enrich_image_suggest', nonce: getNonce(), id: id }
            }).done(function (resp) {
                if (resp && resp.success) {
                    renderSuggestion($tr, resp.data.found ? resp.data.suggestion : null);
                } else {
                    alert((resp && resp.data && resp.data.message) ? resp.data.message : 'Error');
                }
            }).fail(function (xhr) {
                alert('Request failed. ' + (xhr && xhr.responseText ? xhr.responseText.substring(0, 200) : ''));
            }).always(function () { rowSetLoading($tr, false); });
        });

        // Row: Import & set
        $(document).on('click', '.gv-ei-import', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var $tr = $btn.closest('tr');
            var id = $tr.data('id');
            var p = $btn.data('payload');
            if (!p) { console.warn('[GV EI] No payload'); return; }

            rowSetLoading($tr, true);
            $btn.prop('disabled', true).text('Importing…');

            var data = { action: 'gv_enrich_image_import', nonce: getNonce(), id: id, type: p.type };
            if (p.type === 'media') { data.attachment_id = p.attachment_id; } else { data.url = p.url; }

            $.ajax({ url: AJAX, method: 'POST', dataType: 'json', data: data })
                .done(function (resp) {
                    if (resp && resp.success) { $btn.replaceWith('<span>✓ Set</span>'); }
                    else {
                        alert((resp && resp.data && resp.data.message) ? resp.data.message : 'Error');
                        $btn.prop('disabled', false).text('Import & set');
                    }
                })
                .fail(function (xhr) {
                    alert('Request failed. ' + (xhr && xhr.responseText ? xhr.responseText.substring(0, 200) : ''));
                    $btn.prop('disabled', false).text('Import & set');
                })
                .always(function () { rowSetLoading($tr, false); });
        });

        // Bulk: scan
        $(document).on('click', '#gv-ei-bulk-scan', function (e) {
            e.preventDefault();
            var ids = $('.gv-ei-row-check:checked').map(function () { return $(this).val(); }).get();
            if (!ids.length) { alert('Select at least one product.'); return; }
            var $btn = $(this).prop('disabled', true).text('Scanning…');

            $.ajax({
                url: AJAX, method: 'POST', dataType: 'json',
                data: { action: 'gv_enrich_image_bulk', nonce: getNonce(), mode: 'scan', ids: ids }
            }).done(function (resp) {
                if (resp && resp.success) {
                    (resp.data.results || []).forEach(function (r) {
                        var $tr = $('.gv-ei-table tr[data-id="' + r.id + '"]');
                        renderSuggestion($tr, r.suggestion || null);
                    });
                } else {
                    alert((resp && resp.data && resp.data.message) ? resp.data.message : 'Error');
                }
            }).fail(function (xhr) {
                alert('Request failed. ' + (xhr && xhr.responseText ? xhr.responseText.substring(0, 200) : ''));
            }).always(function () { $btn.prop('disabled', false).text('Scan selected'); });
        });

        // Bulk: import
        $(document).on('click', '#gv-ei-bulk-import', function (e) {
            e.preventDefault();
            var ids = $('.gv-ei-row-check:checked').map(function () { return $(this).val(); }).get();
            if (!ids.length) { alert('Select at least one product.'); return; }
            var $btn = $(this).prop('disabled', true).text('Importing…');

            $.ajax({
                url: AJAX, method: 'POST', dataType: 'json',
                data: { action: 'gv_enrich_image_bulk', nonce: getNonce(), mode: 'import', ids: ids }
            }).done(function (resp) {
                if (resp && resp.success) {
                    (resp.data.results || []).forEach(function (r) {
                        if (r.imported) {
                            var $tr = $('.gv-ei-table tr[data-id="' + r.id + '"]');
                            $tr.find('.gv-ei-import').replaceWith('<span>✓ Set</span>');
                        }
                    });
                    alert('Bulk import complete.');
                } else {
                    alert((resp && resp.data && resp.data.message) ? resp.data.message : 'Error');
                }
            }).fail(function (xhr) {
                alert('Request failed. ' + (xhr && xhr.responseText ? xhr.responseText.substring(0, 200) : ''));
            }).always(function () { $btn.prop('disabled', false).text('Import & set image for selected'); });
        });

    });
})();
