function wpctSortElements() {
	jQuery('.widget-liquid-right .widget .widget-content').each(function() {
		var sortableList = jQuery(this).children('#wpct-sortable');
		var consoleContainer = jQuery(this).children('#wpct-sortable-console');
		var buttonSave = jQuery(this).siblings('.widget-control-actions').children().children('.widget-control-save');
		var orderElementsField = jQuery(this).children('.wpct-elements-order-container').children('.elements-order-input');
		
	    sortableList.sortable({
	        update: function(event, ui) {
	
	            consoleContainer.html('');
	            
	            sortableList.children().each(function(i) {
	                var id = jQuery(this).attr('data-element-id')
	                ,order = jQuery(this).index() + 1;
	             
	                consoleContainer.html(consoleContainer.html() + id );
	               
				    var text = consoleContainer.html();
				    orderElementsField.val(text);
	                buttonSave.removeAttr('disabled');
	                buttonSave.attr('value', 'Save');
	            });
	
	        }
	    });
	});

}
    
jQuery(document).ready(function () {
	
	wpctSortElements();
	
	/*selectGfont({
		key: 'AIzaSyDlD2NdRw4MDt-jDoTE_Hz3JqNpl154_qo', // Use You-Google-Fonts-API-Key
		containerFonte: '#selectGFont', 
		containerVariante: '#selectGFontVariante',
		sort: 'popularity',
		onSelectFonte: 'sGFselecionado',
		onSelectVariante: 'sGFselecionado'

	}).then( function() {
	    console.log('OK');

	}).catch( function(erro) {
		console.error(erro);
		
	});

	sGFselecionado = function(fonte, variante, json){
		jQuery(".preview").css('font-family', fonte);
		jQuery(".preview").removeClass('sGFfonte-100 sGFfonte-200 sGFfonte-300 sGFfonte-regular sGFfonte-italic sGFfonte-500 sGFfonte-600 sGFfonte-700 sGFfonte-800 sGFfonte-900 sGFfonte-100italic sGFfonte-200italic sGFfonte-300italic sGFfonte-500italic sGFfonte-600italic sGFfonte-700italic sGFfonte-800italic sGFfonte-900italic');
			jQuery(".preview").addClass( 'sGFfonte-'+variante );
		
		console.log( 'fonte', fonte );
		console.log( 'variante', variante );
		console.log( 'json', json );
	};*/

	
});

jQuery( document ).on( 'widget-added', wpctSortElements );
jQuery( document ).on( 'widget-updated', wpctSortElements );


