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
	
});

jQuery( document ).on( 'widget-added', wpctSortElements );
jQuery( document ).on( 'widget-updated', wpctSortElements );
