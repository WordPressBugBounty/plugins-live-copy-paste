(function (window, document, $, undefined) {
  "use strict";
  var BdThemesLiveCopy = {
    //Initializing properties and methods
    init: function (e) {
      BdThemesLiveCopy.globalSelector();
      BdThemesLiveCopy.methods();
      if ((live_copy_settings_control.only_login_users == 1)) {
        if (document.body.classList.contains('logged-in')) {
          BdThemesLiveCopy.BdThemesLiveCopyBtn();
        }
      } else {
        BdThemesLiveCopy.BdThemesLiveCopyBtn();
      }
    },
    //global properties
    globalSelector: function (e) {
      this._document = $(document);
      this._elItem = this._document.find('[data-elementor-type="wp-page"] > [data-element_type="container"], [data-elementor-type="wp-post"] > [data-element_type="container"], [data-elementor-type="wp-page"] > [data-element_type="section"], [data-elementor-type="wp-post"] > [data-element_type="section"]');
      if (this._elItem.length === 0) {
        this._elItem = this._document.find('.elementor-section.elementor-top-section');
      }
    },

    BdThemesLiveCopyBtn: function () {
      $(this._elItem).each(function () {

        var $this = $(this);
        var hasContent = $.trim($this.find(".elementor-widget-wrap").html()) || $.trim($this.find(".e-con-inner").html() || $.trim($this.find(".e-child").html()) || $.trim($this.find(".elementor-section-wrap").html()) || $.trim($this.find(".elementor-column-wrap").html()) || $.trim($this.find(".elementor-container").html()) || $.trim($this.find(".elementor-row").html()) || $.trim($this.find(".elementor-column").html()) || $.trim($this.find(".elementor-element")));

        $('.magic-button-disabled-no').find('.elementor-element').removeClass("magic-button-disabled-yes");
        
        // Simplified condition
        var isEligible = $this.closest('[data-elementor-type="wp-page"], [data-elementor-type="wp-post"]').length > 0 &&
          ($this.hasClass('elementor-element') || $this.hasClass('elementor-section') || $this.attr('data-element_type') === 'container') &&
          !$this.closest('[data-elementor-type="wp-page"]').hasClass('magic-button-disabled-yes') &&
          !$this.closest('[data-elementor-type="wp-post"]').hasClass('magic-button-disabled-yes') &&
          hasContent;

        if (isEligible) {
          if (live_copy_settings_control.only_specific_section == 1) {
            if ($this.closest("section").hasClass("magic-button-enabled-yes") || $this.hasClass("magic-button-enabled-yes")) {
              $this.append(
                '<div class="bdt-magic-copy-item"><a href="javascript:void(0)" class="bdt-magic-copy-btn">Live Copy</a><span aria-label="Click live copy button to copy this block." data-microtip-position="left" role="tooltip" class="bdt-magic-copy-info"><span class="bdt-magic-copy-icon"></span></span></div>'
              );
            }
          }else {
              $this.append(
                '<div class="bdt-magic-copy-item"><a href="javascript:void(0)" class="bdt-magic-copy-btn">Live Copy</a><span aria-label="Click live copy button to copy this block." data-microtip-position="left" role="tooltip" class="bdt-magic-copy-info"><span class="bdt-magic-copy-icon"></span></span></div>'
              );
            }

          $this.find(".bdt-magic-copy-item").hover(
            function () {
              $this.addClass("bdt-copy-selected");
            },
            function () {
              $this.removeClass("bdt-copy-selected");
            }
          );
        }
      });
    },
    //methods
    methods: function (e) {
      BdThemesLiveCopy.click();
    },
    click: function (e) {
      BdThemesLiveCopy.copyData();
    },
    copyData: function () {
      this._document.on("click", "a.bdt-magic-copy-btn", function (e) {
        var parentSelector = $(this).closest(BdThemesLiveCopy._elItem);
        var _this = $(this);
        var widget_id = parentSelector.data("id");
        var post_id = bdthemes_magic_copy_ajax.post_id;
        var ajax_url = bdthemes_magic_copy_ajax.ajax_url;
        var ajax_nonce = bdthemes_magic_copy_ajax.ajax_nonce;

        $.ajax({
          url: ajax_url,
          type: "post",
          data: {
            action: "live_copy_paste_magic_data_server_request",
            widget_id: widget_id,
            post_id: post_id,
            security: ajax_nonce,
          },
          dataType: "JSON",
          beforeSend: function () {
            _this.text("Copying..");
            parentSelector.css("opacity", "0.7");
          },
          success: function (response) {
            if (response.success) {
              var data = response.data.widget_data;
              data = JSON.stringify(data);

              if (data) {
                // bdtLiveCopyLocalStorage.setItem("magic_copy_data",data, function (data) {
                _this.text("Copied!");
                // }
                // );
                parentSelector.css("opacity", "1");
              } else {
                _this.text("Try again!");
              }

              // new
              if (response.data.copy_data) {
                // Create a temporary textarea element
                var textarea = document.createElement('textarea');
                // Set the value of the textarea to the JSON object
                textarea.value = JSON.stringify(response.data.copy_data);
                // Append the textarea element to the document body
                document.body.appendChild(textarea);
                // Select the text in the textarea
                textarea.select();
                // Copy the selected text to the clipboard
                document.execCommand('copy');
                // Remove the temporary textarea element
                document.body.removeChild(textarea);
              }

            } else {
              var message = response.data.message;
              alert(message);
            }
            setTimeout(function () {
              _this.text("Live Copy");
            }, 3000);
          },
          error: function (errorThrown) {
            _this.text("Try again!");
            setTimeout(function () {
              _this.text("Copy!");
            }, 3000);
          },
        });
      });
    },
  };
  BdThemesLiveCopy.init();
})(window, document, jQuery, bdtLiveCopyLocalStorage);
