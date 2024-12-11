(function($){

    $(document).ready(function () {

        // Move content editor to custom meta box created by every plugin
        var postdivs = $('#postdiv, #postdivrich');

        postdivs.prependTo('#custom_editor .inside');
        postdivs.show();

        $('.purge_cache').on('click', function(e) {
            e.preventDefault();
            var uuid = $(e.target).attr('data-uuid');

            var data = {
                action  : 'oc_purge_article_cache',
                uuid    : uuid
            };

            $.post(ajaxurl, data, function( response ) {
                if( $('#message').length == 0 ) {
                    $('<div id="message">').prependTo('.wrap');
                } else {
                    $('#message').html('');
                }

                var message = '';

                if( response === '1' ) {
                    message = $("<div />").addClass("updated below-h2").html("<p>" + translation.purge_cache_success + "</p>").prependTo("#message");
                } else {
                    message = $("<div />").addClass("updated error below-h2").html("<p>" + translation.purge_cache_failure + "</p>").prependTo("#message");
                };

                setTimeout(function(){
                    $(message).fadeOut();
                }, 5000);

            });

            return false;
        });

        // Unlocking/locking password field when clicking lock icon.
        $(document).on('click', '.password-lock', function(e) {

            var passwordInput = $('#oc_password');
            var disabled = passwordInput.is(':disabled');
            if(disabled) {
                passwordInput.attr('disabled', false);
            }
            else {
                passwordInput.attr('disabled', true);
            }
        });

        //Enable oc test connection from admin settings page
        $(document).on('click', '#test_oc_con_button', function(e){
            e.preventDefault();

            var oc_user = $('#oc_username').val();
            var oc_pass = $('#oc_password').val();
            var oc_url = $('#oc_base').val();
            if( oc_pass === "ew_pl_password" ) {
              oc_pass = "";
            }

            var data  = {
                action  : 'oc_ajax_test_connection',
                oc_user : oc_user,
                oc_pass : oc_pass,
                oc_url	: oc_url
            };

            $.post(ajaxurl, data, function (respons) {
                var message = '';

                if (respons) {
                    message = $("<div />").addClass("updated ok_oc_con").html("<p>" + translation.oc_success + "</p>").prependTo(".oc_settings_form");
                }else{
                    message = $("<div />").addClass("uppdated error settings-error below-h2 ").html("<p>" + translation.oc_failure + "</p>").prependTo(".oc_settings_form");
                }

                setTimeout(function(){
                    $(message).fadeOut();
                }, 2000);

            });

            return false;
        });

        //Enable S3 test connection from admin settings page
        $(document).on('click', '#test_s3_con_button', function(e){
            e.preventDefault();
            var val = $('.storage_option:checked').val();

            if ( val == 's3' ) {
                var s3_key = $('#access_key').val();
                var s3_sec = $('#secret_key').val();
                var s3_buck = $('#s3_bucket').val();

                var data  = {
                    action  : 'oc_ajax_test_s3_connection',
                    s3_key	: s3_key.replace(/\s/g, ''),
                    s3_sec	: s3_sec.replace(/\s/g, ''),
                    s3_buck : s3_buck
                };

                $.post(ajaxurl, data, function (respons) {
                    var message = '';

                    if ( respons === "1" ) {
                        message = $("<div />").addClass("updated ok_oc_con").html("<p>" + translation.s3_success + "</p>").prependTo(".oc_settings_form");
                    }else{
                        message = $("<div />").addClass("updated error settings-error below-h2 ").html("<p>" + translation.s3_failure + "</p>").prependTo(".oc_settings_form");
                    }

                    setTimeout(function(){
                        $(message).fadeOut();
                    }, 3000);

                });
            }

            return false;
        });

        $(document).on('click', '.delete_binding', function(e) {
            $(e.target).parent('div').remove();
        });


        $(document).on('change', '.bindings_selector', function(e) {
            $(e.target).parent('div').find('.binding_input').attr('name', 'oc_options[oc_notifier_bindings]['+ $(e.target).val() +']');
        });

        $(document).on('click', '#oc_notifier20_add_binding', function(e) {

            function render_select_binding( properties ) {
                var wrapper = $('<div>');
                var select = $('<select class="bindings_selector"></select>');
                var input = $('<input type="text" class="binding_input" />');
                var delete_btn = $('<input type="button" class="delete_binding" value="'+translation.remove_button+'" />');

                $(select).append('<option value="" selected disabled>'+ translation.choose_binding +'</option>');
                properties.forEach(function( data ) {
                    $(select).append('<option value="'+data[0]+'">'+data[0]+'</option>');
                });

                $(wrapper).append(select);
                $(wrapper).append(input);
                $(wrapper).append(delete_btn);

                $('.notifier20_binding').prepend($(wrapper));
            }

            var data = {action: 'oc_ajax_get_select_binding'};

            if(typeof $(this).data('contenttype') !== "undefined") {
                data.contenttype = $(this).data('contenttype');
            }

            $.post(ajaxurl, data, function( response ) {
                if( response !== 'null' ) {
                    response_data = JSON.parse( response );
                    render_select_binding( response_data );
                } else {
                    var message = $("<div />").addClass("updated error settings-error below-h2 ").html("<p>" + translation.get_property_error + "</p>").prependTo(".oc_settings_form");

                    setTimeout(function(){
                        $(message).fadeOut();
                    }, 3000);
                }
            });
        });

        function remove_binding_disabled() {
            $('.bindings_selector, .binding_input').each(function(key, ele) {
                $(ele).removeAttr('disabled');
            });
        }

        function remove_password_disabled() {
          $('#oc_password').removeAttr('disabled');
        }

        function set_binding_disabled() {
            $('.bindings_selector, .binding_input').each(function(key, ele) {
                $(ele).attr('disabled', 'disabled');
            });
        }

        function add_binding_remove_button() {
            $('.binding_input').parent('div').append('<input type="button" class="delete_binding" value="'+translation.remove_button+'" />');
        }

        function remove_binding_remove_button() {
            $('.delete_binding').remove();
        }

        function add_binding_button() {
            $('.notifier20_binding').append('<input type="button" id="oc_notifier20_add_binding" class="button" value="'+translation.add_binding+'" />');
        }

        function remove_binding_button() {
            $('#oc_notifier20_add_binding').remove();
        }

        //notifier 2.0 listener
        $(document).on('click', '#oc_notifier20_button', function(e){
            e.preventDefault();

            var btnEl = $(this);
            var type = 'default';
            if(typeof btnEl.data('type') !== 'undefined' && btnEl.data('type') === 'oclist') {
                type = 'oclist';
            }

            function format_url( url ) {
                if( url.search( new RegExp( /^http:|https:\/\//i ) ) === -1 ) {
                    url = "http://" + url;
                }

                return url;
            }

            function show_message( msg ) {
                setTimeout( function() {
                    $(msg).fadeOut();
                }, 3000);
            }

            function get_property_bindings() {
                var properties = {};

                $('.notifier20_binding div').each(function(k, ele) {
                    var key = $(ele).find('select').val();
                    var value = $(ele).find('input').val();

                    if( key !== '' && value !== '' ) {
                        properties[key] =  value;
                    }
                });

                return properties;
            }

            //register
            if( $('#oc_notifier20_registered').val() === '' || type === 'oclist' && typeof btnEl.data('unregister') !== 'undefined' && btnEl.data('unregister') !== true) {

                var notifier_url = $('#oc_notifier20_url').val();
                var formatted_url = format_url( notifier_url );
                var properties = get_property_bindings();
                if( notifier_url ) {
                    var data = {
                        action  : 'oc_ajax_register_notifier',
                        url     : formatted_url,
                        properties : properties,
                        oc_settings_nonce : $('#oc_settings_nonce').val(),
                        type: type
                    }

                    $.post(ajaxurl, data, function( response ) {

                        if( $.trim( response ) === "200" ) {
                            $('#oc_notifier20_button').val( translation.button_unregister ).data('unregister', 'true');
                            $('#oc_notifier20_registered').val( formatted_url );

                            show_message( $("<div />").addClass("updated").html("<p>" + translation.notifier_reg_success + "</p>").prependTo(".oc_settings_form") );
                            set_binding_disabled();
                            remove_binding_remove_button();
                            remove_binding_button();
                        } else {
                            show_message( $("<div />").addClass("updated error").html("<p>" + translation.notifier_reg_failure + "</p>").prependTo(".oc_settings_form") );
                        }
                    });
                } else {
                    show_message( $("<div />").addClass("updated error").html("<p>" + translation.notifier_no_input + "</p>").prependTo(".oc_settings_form") );
                }
            } else {

                var url = $('#oc_notifier20_registered').val();
                if($('#oc_notifier20_url').length > 0) {
                    url = $('#oc_notifier20_url').val();
                }

                //unregister
                var data = {
                    action  : 'oc_ajax_unregister_notifier',
                    url     : url,
                    type: type
                }

                $.post(ajaxurl, data, function( response ) {
                    if( $.trim( response ) == "200" ) {
                        $('#oc_notifier20_button').val( translation.button_register );
                        $('#oc_notifier20_registered').val("");

                        show_message( $("<div />").addClass("updated").html("<p>" + translation.notifier_unreg_success + "</p>").prependTo(".oc_settings_form") );
                        remove_binding_disabled();
                        add_binding_remove_button();
                        add_binding_button();
                    } else {
                        show_message( $("<div />").addClass("updated error").html("<p>" + translation.notifier_unreg_failure + "</p>").prependTo(".oc_settings_form") );
                    }
                });

            }
        });

        $('#options-save').click(function() {
            remove_binding_disabled();
            remove_password_disabled();
        });

        //Fadeout OC Connection test results AND Cache flushed message
        $('div.updated.not_ok_oc_con, div.updated.ok_oc_con, .cache_flushed').delay(1500).fadeOut(2500, "linear");

        set_active_image_service();
        $('.image-service-option').change(function(){
            set_active_image_service();
        });

        //Query builder - Modal pop-up
        var modal_query_builder = $('#modal_query_builder');
        modal_query_builder.dialog({
            'dialogClass'  :'wp-dialog',
            'modal'        :true,
            'autoOpen'     :false,
            'closeOnEscape':true,
            'width'        :500,
            'position'     : { my: "center", at: "center", of: window },
            'beforeClose'  :function () {

                modal_query_builder.empty();
            }
        });

        /*
         * ImEngine / notifier info modal
         */
        var modal_imengine_info = $('.modal_imengine_info');
        modal_imengine_info.dialog({
            'dialogClass'  :'wp-dialog',
            'modal'        :true,
            'autoOpen'     :false,
            'closeOnEscape':true,
            'width'        :600,
            'position'     :'center'
        });

        var modal_notifier_info = $('.modal_notifier_info');
        modal_notifier_info.dialog({
            'dialogClass'  :'wp-dialog',
            'modal'        :true,
            'autoOpen'     :false,
            'closeOnEscape':true,
            'width'        :600,
            'position'     :'center'
        });

        //Display ImEngine info modal on link click
        $(document).on('click', '.modal_imengine_info_link', function () {
            modal_imengine_info.dialog('open');
            return false;
        });
        $(document).on('click', '.modal_notifier_info_link', function () {
            modal_notifier_info.dialog('open');
            return false;
        });

        /*
         * End ImgEngine Notifier Modal
         */

        //Fire Query builder on click
        $(document).on('click', '.query_builder_link', function (e) {

            e.preventDefault();
            var query_field = $(this).parent().find('.oc_query_text_area');

            modal_query_builder.dialog('open');
            var operator = "AND";

            modal_query_builder.append("<div class='ajax_loader'></div>");

            $.get(
                oc_ajax.oc_ajax_url,
                {
                    action:'get_content_types'
                },
                function (data) {

                    modal_query_builder.empty();
                    var parsed_data = $.parseJSON(data);
                    var all_indexfields_arr = [];

                    for (contenttype in parsed_data) {
                        for (indexfield_arr in parsed_data[contenttype]) {
                            for (indexfield in parsed_data[contenttype][indexfield_arr]) {
                                if ($.inArray(parsed_data[contenttype][indexfield_arr][indexfield], all_indexfields_arr) === -1) {

                                    all_indexfields_arr.push(parsed_data[contenttype][indexfield_arr][indexfield]);
                                }
                            }
                        }
                    }

                    all_indexfields_arr.sort();

                    var modal_html = "<h1>" + translation.oc_qb_header + "</h1>";

                    modal_html += "<p>";
                    modal_html += "<select class='query_builder_select' name='indexfield'>";

                    for (indexfield in all_indexfields_arr) {
                        modal_html += "<option value='" + all_indexfields_arr[indexfield] + "'>" + all_indexfields_arr[indexfield] + "</option>";
                    }

                    modal_html += "</select>";

                    modal_html += " = <input type='text' class='compare_value' name='compare_value' value='' />";
                    modal_html += "<a href class='button-primary add_query_rule_button'>" + translation.oc_qb_add_rule + "</a>";

                    modal_html += "</p>";

                    modal_html += "<p>Operator: <select class='operator_select'><option value='AND'>AND</option><option value='OR'>OR</option></select></p>";

                    modal_html += "<h3>" + translation.oc_qb_active_rules + "</h3>";
                    modal_html += "<div class='query_builder_added_rules'>";
                    modal_html += "</div>";

                    modal_html += "<p>" + translation.oc_qb_generated_query + " <br/><textarea class='generated_query_preview' readonly='readonly'></textarea></p>";
                    modal_html += "<p><a href class='button-primary set_query_button'>" + translation.oc_qb_query_button + "</a></p>";

                    modal_query_builder.append(modal_html);

                    $('.add_query_rule_button').click(function () {
                        var indexfield = $('.query_builder_select').val();
                        var value = $('.compare_value').val();

                        var rule_html = "<div class='rule'>";
                        rule_html += "<span class='active_indexfield'>" + indexfield + "</span> = <span class='active_value'>" + value + "</span> <a href class='remove_active_rule_link'></a>";
                        rule_html += "</div>";

                        $('.query_builder_added_rules').append(rule_html);
                        $('.generated_query_preview').val(generate_query_preview(operator));

                        $('.remove_active_rule_link').unbind('click').click(function () {
                            $(this).parent().remove();
                            $('.generated_query_preview').val(generate_query_preview(operator));
                            return false;
                        });

                        return false;
                    });

                    $('.set_query_button').click(function () {
                        query_field.val($('.generated_query_preview').val());
                        modal_query_builder.dialog('close');
                        return false;
                    });

                    $('.operator_select').unbind('change').change(function () {
                        operator = $(this).val();
                        $('.generated_query_preview').val(generate_query_preview(operator));
                    });
                }
            );
        });

        //Ajax test OC query from widget
        $(document).on('click', '.ajax_test_query_link', function (e) {

            //IE fix
            if (!e) {
                e = window.event;
            }
            e.preventDefault();

            var parent = $(this).parent().get(0);

            var query 			= $(parent).find('.oc_query_text_area').val();
            var start 			= $(parent).find('.oc_query_start').val();
            var limit 			= $(parent).find('.oc_query_limit').val();
            var sort 				= $(parent).find('.sort_select').val();
            var that 				= this;
            var result_div 	= $(that).parent().find('.ajax_result_div');

            result_div.empty();

            var loader = '<img src="' + this.href + '" class="loader">';
            result_div.append(loader);
            result_div.show();

            $.post(
                oc_ajax.oc_ajax_url,
                {
                    query         :query,
                    oc_query_start:start,
                    oc_query_limit:limit,
                    oc_query_sort :sort,
                    action        :'test_oc_query'
                },
                function (data) {
                    var parsed_data = $.parseJSON(data);
                    result_div.empty();

                    var header = "<h4>" + translation.oc_query_result + "</h4>";
                    result_div.append(header);

                    for (var object in parsed_data) {
                        var paragraph = "<p>" + object + " : " + parsed_data[object] + "</p>";
                        result_div.append(paragraph);
                    }
                }
            );

            return false;
        });

        function set_active_image_service() {
            var val = $('.image-service-option:checked').val();
            $('.imengine-settings').hide();
            $('.imgix-settings').hide();

            if(val == 'imgix') {
                $('.imgix-settings').show();
            }
            else {
                $('.imengine-settings').show();
            }
        }

        function generate_query_preview(operator) {
            var preview_string = "";
            $('.query_builder_added_rules .rule').each(function (i) {
                preview_string += $('.active_indexfield', $(this)).text() + ':"' + $('.active_value', $(this)).text() + '"';

                if (i + 1 < $('.query_builder_added_rules .rule').length) {
                    preview_string += " " + operator + " ";
                }
            });

            return preview_string;
        }


        if($('td.slug_properties').length > 0) {

            $("#slug_sortable").sortable().disableSelection();

            $.ajax({
                url: oc_ajax.oc_ajax_url,
                data:
                {
                    action:'get_content_types'
                },
                success:function (data) {

                    var parsed_data 	= $.parseJSON(data);
                    var property_arr 	= [];
                    for (indexfield_arr in parsed_data['Article']) {
                        for (property in parsed_data['Article'][indexfield_arr]) {
                            property_arr.push(parsed_data['Article'][indexfield_arr][property]);
                        }
                    }

                    if(property_arr.length > 0) {

                        var options_html;
                        for (var i = 0; i < property_arr.length; i++) {
                            options_html	+= '<option value="' + property_arr[i] + '">' + property_arr[i] + '</option>'
                        }

                        var add_html 	= '';
                        add_html 			+= '<tr>';
                        add_html 			+= '<td>' + translation.article_slug_property + '</td>';
                        add_html 			+= '<td>';
                        add_html 			+= '<select id="add_slug_property_select">';
                        add_html 			+= options_html;
                        add_html 			+= '</select>';
                        add_html			+= '<input type="button" value="' + translation.article_slug_add + '" class="button-primary" id="add_slug_property_button" />';
                        add_html 			+= '</td>';
                        add_html 			+= '</tr>';
                        $('td.slug_properties').parent().after(add_html);

                        $('#add_slug_property_button').click(function(){

                            var property = $('#add_slug_property_select').val();
                            var property_html 	= '';
                            property_html 			+= '<li class="slug_property">';
                            property_html 			+= '<span class="slug_property_name">' + property + '</span>';
                            property_html 			+= '<a href="#" class="slug_property_delete_link"></a>';
                            property_html 			+= '<input type="hidden" name="oc_article_url_options[slug_properties][]" value="' + property + '" />';
                            property_html 			+= '</li>';
                            $('td.slug_properties ul').append(property_html);
                        });

                        /*var department_html = '';
                         department_html 		+= '<tr>';
                         department_html 		+= '<th colspan="2">';
                         department_html 		+= 'If you want to show a category in the url before the articles own slug you can select a property to get that <br />';
                         department_html 		+= 'value from here. If you leave it empty no category will be shown in the URL.';
                         department_html 		+= '</th>';
                         department_html 		+= '</tr>';
                         department_html 		+= '<tr>';
                         department_html 		+= '<td><label for="category_slug">Category property:</label></td>';
                         department_html 		+= '<td>';
                         department_html 		+= '<select id="category_property_select" name="oc_article_url_options[category_property]">';
                         department_html 		+= '<option value="">None</option>';
                         department_html 		+= options_html;
                         department_html			+= '</select>';
                         department_html 		+= '</td>';
                         department_html 		+= '</tr>';
                         $('tr.custom_post_type_slug').after(department_html);
                         $('select#category_property_select').val($('#category_property_hidden_value').val());*/
                    }
                }
            });

            $(document).on('click', '.slug_property_delete_link', function () {
                $(this).parent().remove();
                return false;
            });
        }

    });

})(jQuery);