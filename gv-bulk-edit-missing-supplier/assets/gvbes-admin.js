/* ============================================================================
 * File: assets/gvbes-admin.js  (Manual supplier column + robust errors)
 * ============================================================================ */
(function($){
  const el = $('#gvbes-table');
  const ajaxurl = (window.GVBES && GVBES.ajaxurl) || window.ajaxurl;
  const nonce   = (window.GVBES && GVBES.nonce)   || '';
  let currentPage = 1;

  function msgFromJqXHR(jq){
    try {
      if (jq.responseJSON && jq.responseJSON.data && jq.responseJSON.data.message) {
        return String(jq.responseJSON.data.message);
      }
      if (jq.responseJSON && jq.responseJSON.message) { return String(jq.responseJSON.message); }
      if (jq.responseText) { return String(jq.responseText).slice(0, 400); }
    } catch(e){}
    return 'Network error';
  }

  function escapeHtml(s){
    return String(s).replace(/[&<>\"']/g, function(m){
      return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]);
    });
  }

  function buildSupplierSelect(productId, currentSupplierId){
    let opts = '<option value="">— Select —</option>';
    if (window.GVBES && Array.isArray(GVBES.suppliers)) {
      for (const s of GVBES.suppliers) {
        const sel = (String(s.id) === String(currentSupplierId || '')) ? ' selected' : '';
        opts += '<option value="'+String(s.id)+'"'+sel+'>'+escapeHtml(s.name)+'</option>';
      }
    }
    return '<select class="gvbes-sel" data-id="'+productId+'">'+opts+'</select> ' +
           '<button class="button gvbes-save-sel" data-id="'+productId+'">Save</button>';
  }

  function renderTable(rows, total, page, total_pages){
    let html = '';
    html += '<table class="wp-list-table widefat fixed striped">';
    html += '<thead><tr>'
      +'<th style="width:60px">ID</th>'
      +'<th>Product</th>'
      +'<th style="width:140px">SKU</th>'
      +'<th style="width:120px">Price</th>'
      +'<th style="width:220px">Supplier</th>'
      +'<th style="width:260px">Actions</th>'
      +'</tr></thead><tbody>';

    if(!rows || !rows.length){
      html += '<tr><td colspan="6"><em>'+(GVBES&&GVBES.messages?GVBES.messages.none:'No products found.')+'</em></td></tr>';
    } else {
      rows.forEach(r => {
        html += '<tr data-id="'+r.id+'">'
          +'<td>#'+r.id+'</td>'
          +'<td><a href="'+r.edit+'"><strong>'+escapeHtml(r.title)+'</strong></a></td>'
          +'<td><code>'+(r.sku?escapeHtml(r.sku):'—')+'</code></td>'
          +'<td>'+(r.price?escapeHtml(r.price):'—')+'</td>'
          +'<td>'+ buildSupplierSelect(r.id, r.supplier_id) +' <span class="gvbes-sel-msg" style="margin-left:6px;color:#555"></span></td>'
          +'<td>'
            +'<button class="button button-secondary gvbes-ai" data-id="'+r.id+'">AI Suggest</button> '
            +'<span class="gvbes-suggestion" style="margin-left:8px;color:#555"></span> '
            +'<button class="button button-primary gvbes-apply" data-id="'+r.id+'" disabled>Apply</button>'
          +'</td>'
        +'</tr>';
      });
    }
    html += '</tbody></table>';

    if(total_pages>1){
      html += '<div class="tablenav"><div class="tablenav-pages">';
      html += '<span class="displaying-num">'+total+' items</span>';
      html += '<span class="pagination-links">';
      html += '<a class="first-page button'+(page===1?' disabled':'')+'" data-page="1">«</a>';
      html += '<a class="prev-page button'+(page===1?' disabled':'')+'" data-page="'+(page-1)+'">‹</a>';
      html += '<span class="paging-input">'+page+' of <span class="total-pages">'+total_pages+'</span></span>';
      html += '<a class="next-page button'+(page===total_pages?' disabled':'')+'" data-page="'+(page+1)+'">›</a>';
      html += '<a class="last-page button'+(page===total_pages?' disabled':'')+'" data-page="'+total_pages+'">»</a>';
      html += '</span></div></div>';
    }

    el.html(html);
  }

  function load(page){
    currentPage = page||1;
    el.html('<p><em>'+(GVBES&&GVBES.messages?GVBES.messages.loading:'Loading…')+'</em></p>');
    $.ajax({
      url: ajaxurl,
      data: { action:'gvbes_list', nonce:nonce, paged:currentPage, s: $('#gvbes-s').val()||'' },
      success: function(res){
        if(res && res.success){
          renderTable(res.data.rows, res.data.total, res.data.page, res.data.total_pages);
        } else {
          el.html('<p><em>'+(res && res.data && res.data.message ? escapeHtml(res.data.message) : 'Error loading list.')+'</em></p>');
        }
      },
      error: function(jq){ el.html('<p><em>'+escapeHtml(msgFromJqXHR(jq))+'</em></p>'); }
    });
  }

  // Initial load
  load(1);

  // Pagination
  el.on('click', '.pagination-links a.button', function(e){
    e.preventDefault();
    if($(this).hasClass('disabled')) return;
    const p = parseInt($(this).data('page'),10);
    if(!isNaN(p)) load(p);
  });

  // AI Suggest
  el.on('click', '.gvbes-ai', function(){
    const $btn = $(this);
    const id = parseInt($btn.data('id'),10);
    const $row = $btn.closest('tr');
    const $slot = $row.find('.gvbes-suggestion');
    $btn.prop('disabled', true).text('Thinking…');
    $slot.text('');

    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: { action:'gvbes_ai_suggest', nonce:nonce, product_id:id },
      success: function(res){
        if(res && res.success){
          const s = res.data.suggestion || {};
          $row.data('suggestion', s);
          $slot.html('<strong>'+escapeHtml(s.supplier||'')+'</strong>'
            +(s.sku_suggestion?(' · SKU: <code>'+escapeHtml(s.sku_suggestion)+'</code>'):'')
            +(s.confidence?(' · conf: '+(Math.round(s.confidence*100))+'%'):'')
            +(s.reason?(' – '+escapeHtml(s.reason)):'')
          );
          $row.find('.gvbes-apply').prop('disabled', false);
        } else {
          $slot.text((res && res.data && res.data.message)?res.data.message:'AI error');
        }
      },
      error: function(jq){ $slot.text(msgFromJqXHR(jq)); },
      complete: function(){ $btn.prop('disabled', false).text('AI Suggest'); }
    });
  });

  // Apply AI suggestion
  el.on('click', '.gvbes-apply', function(){
    const $btn = $(this);
    const id = parseInt($btn.data('id'),10);
    const $row = $btn.closest('tr');
    const s = $row.data('suggestion') || {};
    if(!s.supplier){ return; }
    $btn.prop('disabled', true).text('Applying…');

    $.ajax({
      type:'POST',
      url: ajaxurl,
      data:{ action:'gvbes_apply', nonce:nonce, product_id:id, supplier:s.supplier, sku_suggestion:s.sku_suggestion||'' },
      success:function(res){
        if(res && res.success){
          $row.find('.gvbes-suggestion').append(' ✓ '+escapeHtml(res.data.message||'Applied'));
          load(currentPage);
        } else {
          alert((res && res.data && res.data.message)?res.data.message:'Apply failed');
          $btn.prop('disabled', false).text('Apply');
        }
      },
      error:function(jq){ alert(msgFromJqXHR(jq)); $btn.prop('disabled', false).text('Apply'); }
    });
  });

  // Manual supplier select: Save button
  el.on('click', '.gvbes-save-sel', function(){
    const $btn = $(this);
    const id = parseInt($btn.data('id'),10);
    const $row = $btn.closest('tr');
    const $sel = $row.find('.gvbes-sel');
    const supId = $sel.val();
    const $msg = $row.find('.gvbes-sel-msg');
    if(!supId){ $msg.text('Select a supplier first.'); return; }
    $btn.prop('disabled', true).text('Saving…');
    $.ajax({
      type:'POST', url:ajaxurl,
      data:{ action:'gvbes_set_supplier', nonce:nonce, product_id:id, supplier_id:supId },
      success:function(res){
        if(res && res.success){
          $msg.text('Saved');
          load(currentPage);
        } else {
          $msg.text((res && res.data && res.data.message)?res.data.message:'Save failed');
        }
      },
      error:function(jq){ $msg.text(msgFromJqXHR(jq)); },
      complete:function(){ $btn.prop('disabled', false).text('Save'); }
    });
  });

})(jQuery);
