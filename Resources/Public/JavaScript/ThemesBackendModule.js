/**
 * 
 */
define(['jquery'], function (jQuery) {

	var ThemesEditor = {};
	var categoriesFilterSearchField = '';
	var categoriesFilterSearchScope = '';
	var categoriesFilterShowBasic = '';
	var categoriesFilterShowAdvanced = '';
	var categoriesFilterShowExpert = '';
	var categoriesFilterSearchDelay = (function() {
		var timer = 0;
		return function(callback, ms) {
			clearTimeout (timer);
			timer = setTimeout(callback, ms);
		};
	})();

	ThemesEditor.initialize = function() {
		// toggle constant editor
		ThemesEditor.bindEditToggleEvents();
		// make form submittable
		ThemesEditor.bindSaveIconEvent();
		// Bind events
		ThemesEditor.bindCategoriesFilterEvents();
		// Filter initially
		ThemesEditor.categoriesFilterSearch();
		// Display a notice when user tried to edit the default valueQ
		ThemesEditor.bindNoticeForChangingValues();
	};
	
	ThemesEditor.bindNoticeForChangingValues = function() {
		/**
		 * @todo: add translation
		 */
		jQuery('div.field_default').mousedown(function() {
			alert('Enable this field in order to change this value!');
			return false;
		});
		
	};

	ThemesEditor.bindEditToggleEvents = function() {
		jQuery('.showEditForm').click(function() {
			jQuery(this).parents('tr').removeClass('defaultValueIsSelected');
			jQuery(this).parents('tr').find('input[type="hidden"]').attr('value', 'checked');
			jQuery(this).parents('tr').find('input[type="hidden"]').removeAttr('disabled');
		});
		jQuery('.hideEditForm').click(function() {
			jQuery(this).parents('tr').addClass('defaultValueIsSelected');
			jQuery(this).parents('tr').find('input[type="hidden"]').attr('value', '');
			jQuery(this).parents('tr').find('input[type="hidden"]').attr('disabled','disabled');

		});
	};

	ThemesEditor.bindSaveIconEvent = function() {
		jQuery('#saveIcon').click(function() {
			jQuery('#saveableForm').submit();
		});
	};

	ThemesEditor.bindCategoriesFilterEvents = function() {

		// constants quick search
		jQuery('#categoriesFilterSearchField').keyup(function() {
			categoriesFilterSearchDelay(function() {
				ThemesEditor.categoriesFilterSearch();
			}, 500);
		});
		jQuery('#categoriesFilterSearchScope').change(function() {
			ThemesEditor.categoriesFilterSearch();
			ThemesEditor.saveCategoriesSettings();
		});
		jQuery('#categoriesFilterShowBasic').change(function() {
			ThemesEditor.categoriesFilterSearch();
			ThemesEditor.saveCategoriesSettings();
		});
		jQuery('#categoriesFilterShowAdvanced').change(function() {
			ThemesEditor.categoriesFilterSearch();
			ThemesEditor.saveCategoriesSettings();
		});
		jQuery('#categoriesFilterShowExpert').change(function() {
			ThemesEditor.categoriesFilterSearch();
			ThemesEditor.saveCategoriesSettings();
		});

	};

	ThemesEditor.categoriesFilterSearch = function() {
		ThemesEditor.categoriesFilterSearchField = jQuery('#categoriesFilterSearchField').val();
		ThemesEditor.categoriesFilterSearchScope = jQuery('#categoriesFilterSearchScope').val();
		ThemesEditor.categoriesFilterShowBasic = jQuery('#categoriesFilterShowBasic').prop('checked');
		ThemesEditor.categoriesFilterShowAdvanced = jQuery('#categoriesFilterShowAdvanced').prop('checked');
		ThemesEditor.categoriesFilterShowExpert = jQuery('#categoriesFilterShowExpert').prop('checked');

		// Switch scope
		jQuery.each(jQuery("section.constants-group"), function(index, value) {
			if(ThemesEditor.categoriesFilterSearchScope == jQuery(value).attr('data-category') || ThemesEditor.categoriesFilterSearchScope == 'all') {
				jQuery(value).removeClass('hidden');
				jQuery(value).addClass('visible');
			}
			else {
				jQuery(value).addClass('hidden');
				jQuery(value).removeClass('visible');
			}
		});

		// User scope: basic, advanced, expert
		jQuery.each(jQuery("section.constants-group tbody tr"), function(index, value) {
			jQuery(value).addClass('hidden');
			jQuery(value).removeClass('visible');
			if(ThemesEditor.categoriesFilterShowBasic && jQuery(value).attr('data-userscope') == 'basic') {
				jQuery(value).removeClass('hidden');
				jQuery(value).addClass('visible');
			}
			if(ThemesEditor.categoriesFilterShowAdvanced && jQuery(value).attr('data-userscope') == 'advanced') {
				jQuery(value).removeClass('hidden');
				jQuery(value).addClass('visible');
			}
			if(ThemesEditor.categoriesFilterShowExpert && jQuery(value).attr('data-userscope') == 'expert') {
				jQuery(value).removeClass('hidden');
				jQuery(value).addClass('visible');
			}
		});

		// Filter by search word, but only visible items
		jQuery.each(jQuery("section.constants-group tbody tr.visible"), function(index, value) {
			if(ThemesEditor.categoriesFilterSearchField != '') {
				var constantsKey = jQuery(value).find('td.title label').attr('for');
				var constantsTitle = jQuery(value).find('td.title label').html();
				jQuery(value).addClass('hidden');
				jQuery(value).removeClass('visible');
				if(constantsKey.indexOf(ThemesEditor.categoriesFilterSearchField) >= 0 || constantsTitle.indexOf(ThemesEditor.categoriesFilterSearchField) >= 0) {
					jQuery(value).removeClass('hidden');
					jQuery(value).addClass('visible');
				}

			}
		});

		// Hide empty Tables
		jQuery.each(jQuery('section.constants-group.visible table'), function(index, value) {
			if(jQuery(value).find('tbody tr.hidden').length == jQuery(value).find('tbody tr').length) {
				jQuery(value).addClass('hidden');
				jQuery(value).removeClass('visible');
			}
			else {
				jQuery(value).removeClass('hidden');
				jQuery(value).addClass('visible');
			}
		});

		// Hide empty Headlines
		jQuery.each(jQuery('section.constants-group'), function(index, value) {
			if(jQuery(value).find('table.hidden').length == jQuery(value).find('table').length) {
				jQuery(value).addClass('hidden');
				jQuery(value).removeClass('visible');
			}
		});

	};

	/**
	 * Save the selected settings
	 * Fire and forget!
	 */
	ThemesEditor.saveCategoriesSettings = function() {
		var url = jQuery('#categoriesFilterSettingsSaveUrl').val();
		var data = {};
		data.tx_themes_web_themesmod1 = {};
		data.tx_themes_web_themesmod1.searchScope = jQuery('#categoriesFilterSearchScope').val();
		data.tx_themes_web_themesmod1.showBasic = jQuery('#categoriesFilterShowBasic').prop('checked') ? '1' : '0';
		data.tx_themes_web_themesmod1.showAdvanced = jQuery('#categoriesFilterShowAdvanced').prop('checked') ? '1' : '0';
		data.tx_themes_web_themesmod1.showExpert = jQuery('#categoriesFilterShowExpert').prop('checked') ? '1' : '0';
		jQuery.ajax({
			dataType: "json",
			url: url,
			data: data,
			success: function () {
				//alert('done');
			},
			error: function () {
				//alert('error');
			}
		});
	};

	/**
	 * initialize function
	 * */
	return function () {
		ThemesEditor.initialize();
		return ThemesEditor;
	}();
});