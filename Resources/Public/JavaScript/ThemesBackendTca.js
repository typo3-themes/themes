import DocumentService from "@typo3/core/document-service.js";
import jQuery from "jquery";

class ThemesBackendTca {

    initialize() {
        top.TYPO3.ThemesBackendTca = this;
        DocumentService.ready().then(() => {
            top.TYPO3.ThemesBackendTca.initSelectBoxValues();
            jQuery('.contentVariant select').change(function () {
                top.TYPO3.ThemesBackendTca.contentVariantChangeSelectbox(this);
            });
            jQuery('.contentVariant input[type=\'checkbox\']').change(function () {
                top.TYPO3.ThemesBackendTca.contentVariantChangeCheckbox(this);
            });
            jQuery('.contentResponsive select').change(function () {
                top.TYPO3.ThemesBackendTca.contentResponsiveChangeSelectbox(this);
            });
            jQuery('.contentBehaviour select').change(function () {
                top.TYPO3.ThemesBackendTca.contentBehaviourChangeSelectbox(this);
            });
            jQuery('.contentBehaviour input[type=\'checkbox\']').change(function () {
                top.TYPO3.ThemesBackendTca.contentBehaviourChangeCheckbox(this);
            });
            jQuery('.contentColumnSettings select').change(function () {
                top.TYPO3.ThemesBackendTca.contentColumnSettingsChange(this);
            });
            jQuery('.contentEnforceEqualColumnHeight input[type=\'checkbox\']').change(function () {
                top.TYPO3.ThemesBackendTca.contentEnforceEqualColumnHeightChange(this);
            });
        });
    };

    initSelectBoxValues() {
        jQuery.each(jQuery('.contentBehaviour select'), function () {
            // Is already a value selected?
            var alreadySelected = false;
            jQuery.each(jQuery(this).find('option'), function () {
                if (jQuery(this).attr('selected') == 'selected') {
                    alreadySelected = true;
                }
            });
            if (!alreadySelected) {
                jQuery(this).find('option:first-child').attr('selected', 'selected');
                top.TYPO3.ThemesBackendTca.contentBehaviourChangeSelectbox(jQuery(this));
            }
        });
        jQuery.each(jQuery('.contentVariant select'), function () {
            // Is already a value selected?
            var alreadySelected = false;
            jQuery.each(jQuery(this).find('option'), function () {
                if (jQuery(this).attr('selected') == 'selected') {
                    alreadySelected = true;
                }
            });
            if (!alreadySelected) {
                jQuery(this).find('option:first-child').attr('selected', 'selected');
                top.TYPO3.ThemesBackendTca.contentVariantChangeSelectbox(jQuery(this));
            }
        });
        jQuery.each(jQuery('.contentResponsive select'), function () {
            // Is already a value selected?
            var alreadySelected = false;
            jQuery.each(jQuery(this).find('option'), function () {
                if (jQuery(this).attr('selected') == 'selected') {
                    alreadySelected = true;
                }
            });
            if (!alreadySelected) {
                jQuery(this).find('option:first-child').attr('selected', 'selected');
                top.TYPO3.ThemesBackendTca.contentResponsiveChangeSelectbox(jQuery(this));
            }
        });
    };

    contentVariantChangeSelectbox(field) {
        var itemselector = "";
        if (jQuery(field).closest(".t3-form-field-item").index() > 0) {
            itemselector = ".t3-form-field-item";
        } else if (jQuery(field).closest(".t3js-formengine-field-item").index() > 0) {
            itemselector = ".t3js-formengine-field-item";
        } else {
            itemselector = ".form-group";
        }
        var value = jQuery(field).val();
        var prefix = jQuery(field).attr('name');
        var classes = jQuery(field).closest(itemselector).find(".contentVariant input[readonly=\'readonly\']").attr('class').split(' ');
        // Remove all classes with the same prefix
        for (var i = 0; i < classes.length; i++) {
            if (classes[i].substr(0, prefix.length + 1) == prefix + '-') {
                jQuery(field).closest(itemselector).find(".contentVariant input[readonly=\'readonly\']").removeClass(classes[i]);
            }
        }
        // Add the selected value
        jQuery(field).closest(itemselector).find(".contentVariant input[readonly=\'readonly\']").addClass(value);
        var values = jQuery(field).closest(itemselector).find(".contentVariant input[readonly=\'readonly\']").attr("class");
        values = top.TYPO3.ThemesBackendTca.convertClassesForInputValue(values);
        jQuery(field).closest(itemselector).find(".contentVariant input[readonly=\'readonly\']").attr("value", values);
    };

    contentVariantChangeCheckbox(field) {
        var itemselector = "";
        if (jQuery(field).closest(".t3-form-field-item").index() > 0) {
            itemselector = ".t3-form-field-item";
        } else if (jQuery(field).closest(".t3js-formengine-field-item").index() > 0) {
            itemselector = ".t3js-formengine-field-item";
        } else {
            itemselector = ".form-group";
        }
        if (field.checked) {
            jQuery(field).closest(itemselector).find(".contentVariant input[readonly=\'readonly\']").addClass(field.name);
        } else {
            jQuery(field).closest(itemselector).find(".contentVariant input[readonly=\'readonly\']").removeClass(field.name);
        }
        var values = jQuery(field).closest(itemselector).find(".contentVariant input[readonly=\'readonly\']").attr("class");
        values = top.TYPO3.ThemesBackendTca.convertClassesForInputValue(values);
        jQuery(field).closest(itemselector).find(".contentVariant input[readonly=\'readonly\']").attr("value", values);
    };

    contentBehaviourChangeSelectbox(field) {
        var itemselector = "";
        if (jQuery(field).closest(".t3-form-field-item").index() > 0) {
            itemselector = ".t3-form-field-item";
        } else if (jQuery(field).closest(".t3js-formengine-field-item").index() > 0) {
            itemselector = ".t3js-formengine-field-item";
        } else {
            itemselector = ".form-group";
        }
        var value = jQuery(field).val();
        var prefix = jQuery(field).attr('name');
        var classes = jQuery(field).closest(itemselector).find(".contentBehaviour input[readonly=\'readonly\']").attr('class').split(' ');
        // Remove all classes with the same prefix
        for (var i = 0; i < classes.length; i++) {
            if (classes[i].substr(0, prefix.length + 1) == prefix + '-') {
                jQuery(field).closest(itemselector).find(".contentBehaviour input[readonly=\'readonly\']").removeClass(classes[i]);
            }
        }
        // Add the selected value
        jQuery(field).closest(itemselector).find(".contentBehaviour input[readonly=\'readonly\']").addClass(value);
        var values = jQuery(field).closest(itemselector).find(".contentBehaviour input[readonly=\'readonly\']").attr("class");
        values = top.TYPO3.ThemesBackendTca.convertClassesForInputValue(values);
        jQuery(field).closest(itemselector).find(".contentBehaviour input[readonly=\'readonly\']").attr("value", values);
    };

    contentBehaviourChangeCheckbox(field) {
        var itemselector = "";
        if (jQuery(field).closest(".t3-form-field-item").index() > 0) {
            itemselector = ".t3-form-field-item";
        } else if (jQuery(field).closest(".t3js-formengine-field-item").index() > 0) {
            itemselector = ".t3js-formengine-field-item";
        } else {
            itemselector = ".form-group";
        }
        if (field.checked) {
            jQuery(field).closest(itemselector).find(".contentBehaviour input[readonly=\'readonly\']").addClass(field.name);
        } else {
            jQuery(field).closest(itemselector).find(".contentBehaviour input[readonly=\'readonly\']").removeClass(field.name);
        }
        var values = jQuery(field).closest(itemselector).find(".contentBehaviour input[readonly=\'readonly\']").attr("class");
        values = top.TYPO3.ThemesBackendTca.convertClassesForInputValue(values);
        jQuery(field).closest(itemselector).find(".contentBehaviour input[readonly=\'readonly\']").attr("value", values);
    };

    contentResponsiveChangeSelectbox(field) {
        var itemselector = "";
        if (jQuery(field).closest(".t3-form-field-item").index() > 0) {
            itemselector = ".t3-form-field-item";
        } else if (jQuery(field).closest(".t3js-formengine-field-item").index() > 0) {
            itemselector = ".t3js-formengine-field-item";
        } else {
            itemselector = ".form-group";
        }
        var value = jQuery(field).val();
        var prefix = jQuery(field).attr('name');
        var classes = jQuery(field).closest(itemselector).find(".contentResponsive input[readonly=\'readonly\']").attr('class').split(' ');
        // Remove all classes with the same prefix
        for (var i = 0; i < classes.length; i++) {
            if (classes[i].substr(0, prefix.length + 1) == prefix + '-') {
                jQuery(field).closest(itemselector).find(".contentResponsive input[readonly=\'readonly\']").removeClass(classes[i]);
            }
        }
        // Add the selected value
        jQuery(field).closest(itemselector).find(".contentResponsive input[readonly=\'readonly\']").addClass(value);
        var values = jQuery(field).closest(itemselector).find(".contentResponsive input[readonly=\'readonly\']").attr("class");
        values = top.TYPO3.ThemesBackendTca.convertClassesForInputValue(values);
        jQuery(field).closest(itemselector).find(".contentResponsive input[readonly=\'readonly\']").attr("value", values);
    };

    contentColumnSettingsChange(field) {
        var itemselector = "";
        if (jQuery(field).closest(".t3-form-field-item").index() > 0) {
            itemselector = ".t3-form-field-item";
        } else if (jQuery(field).closest(".t3js-formengine-field-item").index() > 0) {
            itemselector = ".t3js-formengine-field-item";
        } else {
            itemselector = ".form-group";
        }
        jQuery.each(jQuery(".contentColumnSettings select[name=\'" + field.name + "\'] option"), function (index, node) {
            jQuery(field).closest(itemselector).find(".contentColumnSettings input[readonly=\'readonly\']").removeClass(node.value);
        });
        jQuery(field).closest(itemselector).find(".contentColumnSettings input[readonly=\'readonly\']").addClass(field.value);
        var values = jQuery(field).closest(itemselector).find(".contentColumnSettings input[readonly=\'readonly\']").attr("class");
        values = top.TYPO3.ThemesBackendTca.convertClassesForInputValue(values);
        jQuery(field).closest(itemselector).find(".contentColumnSettings input[readonly=\'readonly\']").attr("value", values);
    };

    contentEnforceEqualColumnHeightChange(field) {
        var itemselector = "";
        if (jQuery(field).closest(".t3-form-field-item").index() > 0) {
            itemselector = ".t3-form-field-item";
        } else if (jQuery(field).closest(".t3js-formengine-field-item").index() > 0) {
            itemselector = ".t3js-formengine-field-item";
        } else {
            itemselector = ".form-group";
        }
        if (field.checked) {
            jQuery(field).closest(itemselector).find(".contentEnforceEqualColumnHeight input[readonly=\'readonly\']").addClass(field.name);
        } else {
            jQuery(field).closest(itemselector).find(".contentEnforceEqualColumnHeight input[readonly=\'readonly\']").removeClass(field.name);
        }
        var values = jQuery(field).closest(itemselector).find(".contentEnforceEqualColumnHeight input[readonly=\'readonly\']").attr("class");
        values = top.TYPO3.ThemesBackendTca.convertClassesForInputValue(values);
        jQuery(field).closest(itemselector).find(".contentEnforceEqualColumnHeight input[readonly=\'readonly\']").attr("value", values);
    };

    convertClassesForInputValue(values) {
        values = values.replace(/form-control/g, "").trim();
        values = values.replace(/themes-hidden-admin-field/g, "").trim();
        values = values.replace(/\ /g, ",");
        return values;
    };

}

export default new ThemesBackendTca();
