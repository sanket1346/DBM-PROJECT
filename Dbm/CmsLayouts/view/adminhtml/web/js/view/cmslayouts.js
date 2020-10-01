define([
    'jquery'
], function ($) {
    "use strict";

    $.widget('dbm.cmslayouts', {
        /**
         * This method constructs a new widget.
         * @private
         */
        _create: function () {
            this.templates = JSON.parse(this.options.templateHtml);
            this.imgUrls = JSON.parse(this.options.imgUrls);
            this.tplDropdown = $('#cms_default_template');
            this.initObserve();
        },
        initObserve: function () {
            this.loadTemplate();
            this.changeImageUrl();
        },
        loadTemplate: function () {
            var self = this;
            var toggleMCEEditor = $('#togglecms_content');
            var cmsContent = $('#cms_content');
            var btnLoadContent = $('#cms_load_template');
            btnLoadContent.on('click', function () {
                var tplId = self.tplDropdown.val();
                var tpl = self.templates[tplId]["tpl"];
                var replaceBy = self.templates[tplId]["var"];
                var regEx = new RegExp(replaceBy, 'g');
                var html = tpl.replace(regEx, tplId);

                if (cmsContent.css('display') === 'none') {
                    toggleMCEEditor.trigger('click');
                }
                cmsContent.val(html);
            });
        },
        changeImageUrl: function () {
            var imageUrls = this.imgUrls;
            var demoImg = $('#mp-demo-image');
            this.tplDropdown.on('change', function () {
                demoImg.attr('src', imageUrls[$(this).val()]);
            })
        }
    });

    return $.dbm.cmslayouts;
});
