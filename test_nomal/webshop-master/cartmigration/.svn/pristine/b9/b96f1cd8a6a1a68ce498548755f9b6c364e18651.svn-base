$.validator.addMethod("url_http", function (value, element) {
        return /^(http|https)/.test(value);
    }, "Please enter a valid URL."
);
$.extend({
    CartMigration: function (options) {
        var defaults = {
            url: '',
            errorMsg: 'Request timeout or server isn\'t responding, please reload the page.',
            msgTryError: '<p class="error">Request timeout or server isn\'t responding, please try again.</p>',
            msgTryWarning: '<p class="warning">Please try again.</p>',
            msgTryImport: '<p class="success"> - Resuming import ...</p>',
            resume_process: 'clear',
            resume_type: 'taxes',
            delay: 1000,
            retry: 30000
        };
        const RESUME = 1;
        const SOURCE_CART_TYPE = 2;
        const SOURCE_CART_URL = 3;
        const SOURCE_CART_TOKEN = 4;
        const TARGET_CART_TYPE = 5;
        const TARGET_CART_URL = 6;
        const TARGET_CART_TOKEN = 7;
        const CHOOSE_ENTITIES = 8;
        const OPTION_RECENT = 9;
        const OPTION_CLEAR = 10;
        const OPTION_PRE_CUS = 11;
        const OPTION_PRE_ORDER = 12;
        const OPTION_PRE_PRODUCT = 13;
        const OPTION_SEO = 14;
        const MAP_LANGUAGE = 15;
        const MAP_CATEGORY = 16;
        const MAP_ATTRIBUTE = 17;
        const MAP_ORDER_STATUS = 18;
        const MAP_CURRENCY = 19;
        const MAP_CUSTOMER_GROUP = 20;
        const BACK = 0;
        const SETUP = 1;
        const CONFIGURATION = 2;
        const MIGRATION = 3;
        const FINISH = 4;
        var supportHeightStart = parseInt($('#support').css('height')) - parseInt($('#support_content').css('height'))
        var isChangeSupport = true;
        var settings = $.extend(defaults, options);
        var activeProgress = SETUP;
        var oldTypeSupport = SOURCE_CART_TYPE;
        function setActiveProgress(progress) {
            var circle = "#progress-"+progress;
            var bar = "#bar-"+(progress-1);
            $(circle).addClass('active');
            $(bar).addClass("done");
            activeProgress = progress;
        }

        function setDoneProgress(progress) {
            var circle = "#progress-"+progress;
            var bar = "#bar-"+(progress-1);

            if($(circle).hasClass('active')){
                $(circle).removeClass('active');
            }
            $(circle).addClass('done');
            if(progress > SETUP){
                if(!$(bar).hasClass('done')){
                    $(bar).addClass('done');
                }
            }
        }

        function showScreen(screen) {
            var setup = $('#setup-content'),
                config = $('#config-content'),
                migration = $('#import-content'),
                confirm = $('#confirm-content');
            confirm.hide();
            switch (screen){
                case SETUP:
                    setup.show();
                    config.hide();
                    migration.hide();
                    showSupport()
                    break;
                case CONFIGURATION:
                    setup.hide();
                    config.show();
                    migration.hide();
                    showSupport()
                    break;
                case MIGRATION:
                    setup.hide();
                    config.hide();
                    migration.show();
                    hideSupport();
                    break;
            }
        }

        function consoleLog(msg, elm) {
            var element = $(elm);
            if (element.length > 0) {
                element.append(msg);
                element.animate({scrollTop: element.prop("scrollHeight")});
            }
        }
        function fixWidthSupport() {
            var width = parseInt($('#parent_support').css('width'));
            $('#support').css('width',width);
            var heightProgress = parseInt($('.progress_new').css('height'));
            $('#main').css('margin-top',heightProgress-10);
        }


        function consoleLogStorage(msg) {
            consoleLog(msg, '#console-log-storage');
        }

        function consoleLogImport(msg) {
            consoleLog(msg, '#console-log-import');
        }

        function checkElementShow(elm) {
            var check = $(elm).is(':visible');
            return check;
        }

        function showTryImport(elm) {
            var element = $(elm);
            if (element.length > 0) {
                element.find('.try-import').show();
                deleteCookie();
            }
        }

        function hideTryImport(elm) {
            var element = $(elm).find('.try-import');
            if (element.length !== 0) {
                consoleLogImport(settings.msgTryImport);
                element.hide();
            }
            createCookie(1);
        }

        function checkOptionDuplicate(elm) {
            var check = new Array();
            var exists = false;
            $(elm).each(function (index, value) {
                var element = $(value);
                var elm_val = element.val();
                if (elm_val) {
                    check[index] = elm_val;
                    exists = true;
                }
            });
            if (!exists) {
                return false;
            }
            var result = true;
            check.forEach(function (value, index) {
                check.forEach(function (value_tmp, index_tmp) {
                    if (value_tmp === value && index !== index_tmp) {
                        result = false;
                    }
                });
            });
            return result;
        }

        function createCookie(value) {
            var date = new Date();
            date.setTime(date.getTime() + (24 * 60 * 60 * 1000));
            var expires = "; expires=" + date.toGMTString();
            document.cookie = "cart_migration=" + value + expires + "; path=/";
        }

        function getCookie() {
            var nameEQ = "cart_migration=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) === ' ')
                    c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0)
                    return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        function deleteCookie() {
            var date = new Date();
            date.setTime(date.getTime() + (-1 * 24 * 60 * 60 * 1000));
            var expires = "; expires=" + date.toGMTString();
            document.cookie = "cart_migration=" + expires + "; path=/";
        }

        function checkCookie() {
            var check = getCookie();
            var result = false;
            if (check === '1') {
                result = true;
            }
            return result;
        }

        function showProcessBar(elm, total, imported, error, point) {
            var element = $(elm);
            if (element.length > 0) {
                showProcessBarWidth(element, point);
                showProcessBarConsole(element, total, imported, error);
            } else {
                return false;
            }
        }

        function showProcessBarWidth(element, point) {
            var pbw = element.find('.process-bar-width');
            if (pbw.length !== 0 && point !== null) {
                pbw.css({
                    'display': 'block',
                    'width': point + '%'
                });
            } else {
                return false;
            }
        }

        function showProcessBarConsole(element, total, imported, error) {
            var pbc = element.find('.console-log');
            if (pbc.length !== 0) {
                var html = 'Imported: ' + imported + '/' + total + ', Errors: ' + error;
                pbc.show();
                pbc.html(html);
            } else {
                return false;
            }
        }

        function triggerClick(elm) {
            var par_elm = elm + ' .try-import';
            var check_show = checkElementShow(par_elm);
            var button = $(par_elm).children('div');
            if (check_show) {
                button.trigger('click');
            }
        }

        function autoRetry(elm) {
            if (settings.retry > 0) {
                setTimeout(function () {
                    triggerClick(elm)
                }, settings.retry);
            }
        }

        function checkSelectEntity() {
            var result = false;
            if ($('input:checkbox:checked', '#entity-section').length > 0) {
                $('#error-entity-select').hide();
                result = true;
            } else {
                $('#error-entity-select').show();
            }
            return result;
        }

        function checkSelectShop() {
            if ($('#shop-section').length < 1) {
                return true;
            }
            var result = checkOptionDuplicate('#shop-section select');
            if (result === true) {
                $('#error-site-duplicate').hide();
            } else {
                $('#error-site-duplicate').show();
            }
            return result;
        }

        function checkSelectLang() {
            if ($('#language-section').length < 1) {
                return true;
            }
            var result = checkOptionDuplicate('#language-section select');
            if (result === true) {
                $('#error-language-duplicate').hide();
            } else {
                $('#error-language-duplicate').show();
            }
            return result;
        }

        function checkSelectCategory() {
            if ($('#category-section').length < 1) {
                return true;
            }
            var result = checkOptionDuplicate('#category-section select');
            if (result === true) {
                $('#error-category-root-duplicate').hide();
            } else {
                $('#error-category-root-duplicate').show();
            }
            return result;
        }

        function checkSelectAttribute() {
            if ($('#attribute-section').length < 1) {
                return true;
            }
            var result = checkOptionDuplicate('#attribute-section select');
            if (result === true) {
                $('#error-attribute-duplicate').hide();
            } else {
                $('#error-attribute-duplicate').show();
            }
            return result;
        }

        function storageData() {
            createCookie(1);
            $.ajax({
                url: settings.url,
                type: 'post',
                dataType: 'json',
                data: {
                    process: 'storageData'
                },
                success: function (response, textStatus, errorThrown) {
                    if (response.msg != '') {
                        consoleLogStorage(response.msg);
                    }
                    if (response.result == 'success') {
                        var formStorage = $('#form-storage');
                        var data = formStorage.serialize();
                        $.ajax({
                            url: settings.url,
                            type: 'POST',
                            dataType: 'json',
                            data: data,
                            success: function (response_2, textStatus, errorThrown) {
                                if (response_2.result == 'success') {
                                    deleteCookie(1);
                                    $('#storage-content').hide();
                                    $(response_2.elm).html(response_2.html);
                                    $(response_2.elm).show();
                                    setActiveProgress('config');
                                    setDoneProgress('setup');
                                } else {
                                    consoleLogStorage(response_2.msg);
                                    $('#btn-retry-storage').show();
                                }
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                consoleLogStorage(settings.msgTryError);
                                $('#btn-retry-storage-wrap').show();
                            }
                        });
                    } else if (response.result == 'process') {
                        setTimeout(storageData, settings.delay);
                    } else {
                        deleteCookie();
                        $('#btn-retry-storage').show();
                        if (settings.retry > 0) {
                            setTimeout(function () {
                                var check_show = checkElementShow('#btn-retry-storage-wrap');
                                if (check_show) {
                                    $('#btn-retry-storage').trigger('click');
                                }
                            }, settings.retry);
                        }
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    consoleLogStorage(settings.msgTryError);
                    $('#btn-retry-storage').show();
                    if (settings.retry > 0) {
                        setTimeout(function () {
                            var check_show = checkElementShow('#btn-retry-storage-wrap');
                            if (check_show) {
                                $('#btn-retry-storage').trigger('click');
                            }
                        }, settings.retry);
                    }
                }
            });
        }

        function clearData() {
            createCookie(1);
            $.ajax({
                url: settings.url,
                type: 'POST',
                dataType: 'json',
                data: {
                    process: 'clear'
                },
                success: function (response, textStatus, errorThrown) {
                    if (response.msg != '') {
                        consoleLogImport(response.msg);
                    }
                    if (response.result == 'success') {
                        $('#process-clear-data').hide();
                        setTimeout(function () {
                            prepareImport();
                        }, settings.delay);
                    } else if (response.result == 'process') {
                        setTimeout(clearData, settings.delay);
                    } else if (response.result == 'error') {
                        $('#retry-clear-shop-wrap').show();
                        if (settings.retry > 0) {
                            setTimeout(function () {
                                var check_show = checkElementShow('#retry-clear-shop-wrap');
                                if (check_show) {
                                    $('#retry-clear-shop').trigger('click');
                                }
                            }, settings.retry);
                        }
                    } else {
                        $('#process-clear-data').hide();
                        setTimeout(function () {
                            prepareImport();
                        }, settings.delay);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    consoleLogImport(settings.msgTryError);
                    $('#retry-clear-shop-wrap').show();
                    if (settings.retry > 0) {
                        setTimeout(function () {
                            var check_show = checkElementShow('#retry-clear-shop-wrap');
                            if (check_show) {
                                $('#retry-clear-shop').trigger('click');
                            }
                        }, settings.retry);
                    }
                }
            });
        }

        function prepareImport() {
            var process_bar_id = '#process-taxes';
            var data = {
                process: 'prepareImport'
            };
            $.ajax({
                url: settings.url,
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function (response, textStatus, errorThrown) {
                    if (response.msg != '') {
                        consoleLogImport(response.msg);
                    }
                    if (response.result == 'success') {
                        setTimeout(function () {
                            importData('taxes');
                        }, settings.delay);
                    } else {
                        showTryImport(process_bar_id);
                        autoRetry(process_bar_id);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    consoleLogImport(settings.msgTryImport);
                    showTryImport(process_bar_id);
                    autoRetry(process_bar_id);
                }
            });
        }

        function importData(type) {
            createCookie(1);
            var data = {
                'process': 'import',
                'type': type
            };
            var process_bar_id = '#process-' + type;
            var process_bar = $(process_bar_id);
            $.ajax({
                url: settings.url,
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function (response, textStatus, errorThrown) {
                    // alert(123);
                    if (response.msg != '') {
                        consoleLogImport(response.msg);
                    }
                    if (response.result == 'success') {
                        showProcessBar(process_bar, response.process.total, response.process.imported, response.process.error, response.process.point);
                        $('#form-import-submit-wrap').show();
                        setDoneProgress(MIGRATION);
                        activeProgress = 0;
                        // setDoneProgress(MIGRATION);
                    } else if (response.result == 'process') {
                        showProcessBar(process_bar, response.process.total, response.process.imported, response.process.error, response.process.point);
                        var next_type = response.process.next;
                        setTimeout(function () {
                            importData(next_type);
                        }, settings.delay);
                    } else {
                        showTryImport(process_bar_id);
                        autoRetry(process_bar_id);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    consoleLogImport(settings.msgTryImport);
                    showTryImport(process_bar_id);
                    autoRetry(process_bar_id);
                }
            });
        }

        return run();

        function run() {

            deleteCookie();
            fixWidthSupport();

            $(window).on('beforeunload', function () {
                var check = checkCookie();
                if (check === true) {
                    return "Migration is in progress, leaving current page will stop it! Are you sure want to stop?";
                }
            });
            $(window).on('resize', function () {
                fixWidthSupport();
            });
            $(document).on('click', '#form-resume-submit', function () {
                resumeSubmit();
            });

            $(document).on('change', '#source-cart-select', function () {
                var cart_type = $('#source-cart-select').val();
                var _this = $(this);
                _this.prop('disabled', true);
                $.ajax({
                    url: settings.url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        process: 'changeSource',
                        source_cart_type: cart_type
                    },
                    success: function (response, textStatus, errorThrown) {
                        if (response.result == 'show') {
                            $('#source-info').html(response.html);
                            $('#support_content_4').html(response.support)
                            // if (response.show_next == 'true') {
                            //     $('#form-source-submit-wrap').css({display: 'block'});
                            // } else {
                            //     $('#form-source-submit-wrap').css({display: 'none'});
                            // }
                        } else {
                            alert(response.msg);
                        }
                        _this.prop('disabled', false);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert(settings.errorMsg);
                        _this.prop('disabled', false);
                    }
                });
            });
            $(document).on('change', '#target-cart-select', function () {
                var cart_type = $('#target-cart-select').val();
                var _this = $(this);
                _this.prop('disabled', true);
                $.ajax({
                    url: settings.url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        process: 'changeTarget',
                        target_cart_type: cart_type
                    },
                    success: function (response, textStatus, errorThrown) {
                        if (response.result == 'show') {
                            $('#target-info').html(response.html);
                            $('#support_content_7').html(response.support)
                        } else {
                            alert(response.msg);
                        }
                        _this.prop('disabled', false);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        _this.prop('disabled', false);
                    }
                });
            });
            $(document).on('click', '#form-upload-submit', function () {
                var check = $('#form-source').valid();
                if (!check) {
                    return false;
                }
                var uploadWrap = $('#form-upload-submit-wrap'),
                    uploadLoading = $('#form-upload-loading'),
                    process = $('#form-source-process'),
                    sourceCartSelect = $('#source-cart-select'),
                    sourceCartSubmitWrap = $('#form-source-submit-wrap');
                $('#recent-content').hide();
                $('#resume-content').hide();
                $('.upload-result').html('');
                process.val('upload');
                $('#form-source').ajaxSubmit({
                    url: settings.url,
                    dataType: 'json',
                    beforeSubmit: function (formData, formObject, formOptions) {
                        uploadWrap.hide();
                        uploadLoading.show();
                        sourceCartSelect.prop('disabled', true);
                    },
                    success: function (response, textStatus, errorThrown) {
                        if (response.result == 'success') {
                            $.each(response.msg, function (item) {
                                var elm = this.elm;
                                var msg = this.msg;
                                $(elm).html(msg);
                            });
                            sourceCartSubmitWrap.show();
                        } else {
                            alert(response.msg);
                        }
                        uploadWrap.show();
                        uploadLoading.hide();
                        sourceCartSelect.prop('disabled', false);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        uploadWrap.show();
                        uploadLoading.hide();
                        sourceCartSelect.prop('disabled', false);
                        alert(settings.errorMsg);
                    }
                });
            });

            $(document).on('click', '#form-source-submit', function () {
                var check = $('#form-source').valid();
                if (!check) {
                    return false;
                }
                var loading = $('#form-source-loading'),
                    process = $('#form-source-process'),
                    formSource = $('#form-source');
                $('#recent-content').hide();
                $('#resume-content').hide();
                process.val('setupSource');
                var data = formSource.serialize();
                loading.show();
                $.ajax({
                    url: settings.url,
                    type: 'POST',
                    dataType: 'json',
                    data: data,
                    success: function (response, textStatus, errorThrown) {
                        // alert(JSON.stringify(response))
                        loading.hide();
                        if (response.result == 'success') {
                            $('#target-content').html(response.html);
                            $('#target-content').show();
                            $('#source-content').hide();
                        } else {
                            alert(response.msg);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        loading.hide();
                        alert(settings.errorMsg);
                    }
                });
            });

            $(document).on('click', '#form-setup-submit', function () {
                setupSubmit();
            });


            $(document).on('click', '#form-target-submit', function () {
                var check = $('#form-target').valid();
                if (!check) {
                    return false;
                }
                var loading = $('#form-target-loading'),
                    formTarget = $('#form-target');
                var data = formTarget.serialize();
                loading.show();
                $.ajax({
                    url: settings.url,
                    type: 'POST',
                    dataType: 'json',
                    data: data,
                    success: function (response, textStatus, errorThrown) {
                        if (response.result == 'success') {
                            $('#target-content').hide();
                            $(response.elm).html(response.html);
                            $(response.elm).show();
                            if (response.storage == 'yes') {
                                setTimeout(storageData, settings.delay);
                            } else {
                                setActiveProgress('config');
                                setDoneProgress('setup');
                            }
                        } else {
                            alert(response.msg);
                        }
                        loading.hide();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        loading.hide();
                        alert(settings.errorMsg);
                    }
                });
            });

            $(document).on('click', '#form-target-back', function () {
                $('#target-content').hide();
                $('#source-content').show();
            });

            $(document).on('click', '#btn-retry-storage', function () {
                $('#btn-retry-storage').hide();
                setTimeout(storageData, settings.delay);
            });

            $(document).on('click', '#form-config-submit', function () {

                configSubmit(false);
            });

            $(document).on('click', '#form-config-back', function () {
                $('#config-content').hide();
                $('#setup-content').show();
                setActiveProgress('setup');
                setDoneProgress('config');
            });

            $(document).on('click', '#form-confirm-submit', function () {
                var loading = $('#form-confirm-loading'),
                    formConfirm = $('#form-confirm');
                var data = formConfirm.serialize();
                loading.show();

                confirmSubmit(false);
            });

            $(document).on('click', '#form-confirm-back', function () {
                $('#config-content').show();
                $('#confirm-content').hide();
                showSupport();
            });

            $(document).on('click', '#retry-clear-shop', function () {
                $('#retry-clear-shop-wrap').hide();
                setTimeout(clearData, settings.delay);
            });

            $(document).on('click', '#try-import-taxes', function () {
                hideTryImport('#process-taxes');
                setTimeout(function () {
                    importData('taxes');
                }, settings.delay);
            });

            $(document).on('click', '#try-import-manufacturers', function () {
                hideTryImport('#process-manufacturers');
                setTimeout(function () {
                    importData('manufacturers');
                }, settings.delay);
            });

            $(document).on('click', '#try-import-categories', function () {
                hideTryImport('#process-categories');
                setTimeout(function () {
                    importData('categories');
                }, settings.delay);
            });

            $(document).on('click', '#try-import-products', function () {
                hideTryImport('#process-products');
                setTimeout(function () {
                    importData('products');
                }, settings.delay);
            });

            $(document).on('click', '#try-import-customers', function () {
                hideTryImport('#process-customers');
                setTimeout(function () {
                    importData('customers');
                }, settings.delay);
            });

            $(document).on('click', '#try-import-orders', function () {
                hideTryImport('#process-orders');
                setTimeout(function () {
                    importData('orders');
                }, settings.delay);
            });

            $(document).on('click', '#try-import-reviews', function () {
                hideTryImport('#process-reviews');
                setTimeout(function () {
                    importData('reviews');
                }, settings.delay);
            });

            $(document).on('click', '#try-import-pages', function () {
                hideTryImport('#process-pages');
                setTimeout(function () {
                    importData('pages');
                }, settings.delay);
            });
            $(document).on('click', '#try-import-blocks', function () {
                hideTryImport('#process-blocks');
                setTimeout(function () {
                    importData('blocks');
                }, settings.delay);
            });
            $(document).on('click', '#try-import-transactions', function () {
                hideTryImport('#process-transactions');
                setTimeout(function () {
                    importData('transactions');
                }, settings.delay);
            });
            $(document).on('click', '#try-import-rules', function () {
                hideTryImport('#process-rules');
                setTimeout(function () {
                    importData('rules');
                }, settings.delay);
            });
            $(document).on('click', '#try-import-cartrules', function () {
                hideTryImport('#process-cartrules');
                setTimeout(function () {
                    importData('cartrules');
                }, settings.delay);
            });
            $(document).on('click', '#form-import-submit', function () {
                var loading = $('#form-import-loading'),
                    formImport = $('#form-import');
                var data = formImport.serialize();
                loading.show();
                $('#form-import-submit-wrap').hide();
                $.ajax({
                    url: settings.url,
                    type: 'POST',
                    dataType: 'json',
                    data: data,
                    success: function (response, textStatus, errorThrown) {
                        loading.hide();
                        consoleLogImport(response.msg);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        loading.hide();
                        consoleLogImport('<p class="error">Request timeout or server isn\'t responding.</p>');
                    }
                });
            });

            $(document).on('click', '.entity-label', function () {
                $(this).parent().children('input').trigger('click');
            });

            $(document).on('click', '#select-all-entities', function () {
                $('.lv0').find('input').prop('checked', $(this).prop('checked'));
            });

            $(document).on('click', '.lv2', function () {
                var _this = $(this);
                if (_this.prop('checked') === true) {
                    _this.parents('.lv0').find('.lv1').prop('checked', true);
                }
            });

            $(document).on('click', '.lv1', function () {
                var _this = $(this);
                if (_this.prop('checked') === false) {
                    _this.parents('.lv0').find('.lv2').prop('checked', false);
                }
            });

            $(document).on('click', '#choose-seo', function () {
                $('#seo_plugin').slideToggle();
            });

            $(document).on('click', '#next-support', function () {
                nextSupport();
            });

            $(document).on('click', '#pre-support', function () {
                previousSupport();
            });

            $(document).on('mouseover','#form-source-type',
                function () {
                    getSupport(SOURCE_CART_TYPE);
                }
            );

            $(document).on('mouseover','#form-source-url',
                function () {
                    getSupport(SOURCE_CART_URL) ;
                }
            );

            $(document).on('mouseover','#form-source-token',
                function () {
                    getSupport(SOURCE_CART_TOKEN) ;
                }
            );

            $(document).on('mouseover','#form-target-type',
                function () {
                    getSupport(TARGET_CART_TYPE) ;

                }
            );

            $(document).on('mouseover','#form-target-url',
                function () {
                    getSupport(TARGET_CART_URL) ;
                }
            );

            $(document).on('mouseover','#form-target-token',
                function () {
                    getSupport(TARGET_CART_TOKEN) ;
                }
            );

            $(document).on('mouseover','#form-entities',function () {
                getSupport(CHOOSE_ENTITIES) ;
            });

            $(document).on('mouseover','#option_recent',function () {
                getSupport(OPTION_RECENT) ;
            });

            $(document).on('mouseover','#option_clear',function () {
                getSupport(OPTION_CLEAR) ;
            });

            $(document).on('mouseover','#option_pre_cus',function () {
                getSupport(OPTION_PRE_CUS) ;
            });

            $(document).on('mouseover','#option_pre_order',function () {
                getSupport(OPTION_PRE_ORDER) ;
            });

            $(document).on('mouseover','#option_pre_prd',function () {
                getSupport(OPTION_PRE_PRODUCT) ;
            });

            $(document).on('mouseover','#option_seo',function () {
                getSupport(OPTION_SEO) ;
            });

            $(document).on('mouseover','#language-section',function () {
                getSupport(MAP_LANGUAGE) ;
            });

            $(document).on('mouseover','#category-section',function () {
                getSupport(MAP_CATEGORY) ;
            });

            $(document).on('mouseover','#attribute-section',function () {
                getSupport(MAP_ATTRIBUTE) ;
            });

            $(document).on('mouseover','#order-status-section',function () {
                getSupport(MAP_ORDER_STATUS) ;
            });

            $(document).on('mouseover','#currency-section',function () {
                getSupport(MAP_CURRENCY) ;
            });

            $(document).on('mouseover','#customer-group-section',function () {
                getSupport(MAP_CUSTOMER_GROUP) ;
            });

            $(document).on('mouseover','#progress-1',function () {
                if(activeProgress == MIGRATION){
                    $('#progress-1').css('cursor','not-allowed');
                }else{
                    $('#progress-1').css('cursor','pointer');

                }
            });

            $(document).on('mouseover','#progress-2',function () {
                if(activeProgress == MIGRATION){
                    $('#progress-2').css('cursor','not-allowed');
                }else {
                    $('#progress-2').css('cursor','pointer');

                }
            });

            $(document).on('click','#progress-1',function () {
                switch (activeProgress){
                    case SETUP:
                        break;
                    case MIGRATION:
                        break;
                    default:
                        showScreen(SETUP);
                        break;
                }
            });
            $(document).on('click','#progress-2',function () {
                switch (activeProgress){
                    case CONFIGURATION:
                        $('#setup-content').hide();
                        if($('#progress-2').hasClass('done')){
                            $('#confirm-content').show();
                        }else {
                            $('#config-content').show();

                        }
                        break;
                    case SETUP:
                        setupSubmit();
                        break;
                    case MIGRATION:
                        break;
                    default:
                        $('#config-content').hide();
                        $('#setup-content').show();
                        break;
                }
            });

            $(document).on('click','#progress-3',function () {
                switch (activeProgress){
                    case CONFIGURATION:
                        configSubmit(true);
                        break;
                    case SETUP:
                        if($('#resume-content').is(":visible")){
                            if(confirm('Are you resume?')){
                                resumeSubmit();
                                break;
                            }
                            $('#resume-content').hide();
                        }
                        setupSubmit();
                        break;
                    case MIGRATION:
                        break;
                    default:
                        $('#config-content').hide();
                        $('#setup-content').show();
                        break;
                }
            });
        }


        function getSupport(type) {
            if(oldTypeSupport == type){
                return;
            }
            // if(isChangeSupport){
                isChangeSupport = false;
                var support = $('#support_content');
                var old_support = $('#support_content_'+oldTypeSupport);
                var new_support = $('#support_content_'+type);
                old_support.removeClass('display-block');
                old_support.addClass('display-none');
                new_support.removeClass('display-none');
                new_support.addClass('display-block');
                var newHeight = parseInt(support.css('height')) + supportHeightStart
                $('#support').css('height',newHeight);
                // $('#support').animate({'max-height': newHeight},300,function () {
                //     isChangeSupport = true;
                // });
                oldTypeSupport = type;
            // }

        }

        function nextSupport() {
            var type = 0;
            if(oldTypeSupport == MAP_CUSTOMER_GROUP){
                type  = SOURCE_CART_TYPE;
            }else{
                type = oldTypeSupport +1;
            }
            getSupport(type);
        }

        function previousSupport() {
            var type = 0;
            if(oldTypeSupport == SOURCE_CART_TYPE){
                type  = MAP_CUSTOMER_GROUP;
            }else{
                type = oldTypeSupport -1;
            }
            getSupport(type);
        }

        function setupSubmit() {
            var check = $('#form-setup').valid();
            if (!check) {
                return false;
            }
            var loading = $('#form-loading'),
                // process = $('#form-setup-process'),
                formSetup = $('#form-setup');
            $('#recent-content').hide();
            $('#resume-content').hide();
            // process.val('setupSource');
            var data = formSetup.serialize();
            loading.show();
            $.ajax({
                url: settings.url,
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function (response, textStatus, errorThrown) {
                    // alert(JSON.stringify(response))
                    loading.hide();
                    consoleLog(JSON.stringify(response))
                    if (response.result == 'success') {
                        $('#setup-content').hide();
                        $(response.elm).html(response.html);
                        $(response.elm).show();
                        if (response.storage == 'yes') {
                            setTimeout(storageData, settings.delay);
                        } else {
                            setActiveProgress(CONFIGURATION);
                            setDoneProgress(SETUP);
                        }
                    } else {
                        alert(response.msg);
                    }
                    // loading.hide();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    loading.hide();
                    alert(settings.errorMsg);
                }
            });
        }

        function configSubmit(isSetupFast) {
            if (checkSelectShop() !== true
                || checkSelectLang() !== true
                || checkSelectCategory() !== true
                //|| checkSelectAttribute() !== true
                || checkSelectEntity() !== true) {
                alert('To proceed, please check and correct your configurations highlighted in red.');
                if($('#setup-content').is(":visible")){
                    showScreen(CONFIGURATION);
                }
                return false;
            }
            var loading = $('#form-config-loading'),
                formConfig = $('#form-config');
            var data = formConfig.serialize();
            loading.show();
            $.ajax({
                url: settings.url,
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function (response, textStatus, errorThrown) {
                    if (response.result == 'success') {
                        if(!isSetupFast){
                            setDoneProgress(CONFIGURATION);
                            $('#config-content').hide();
                            hideSupport();
                            $('#confirm-content').html(response.html);
                            $('#confirm-content').show();

                        }else{
                            confirmSubmit(isSetupFast);
                        }

                    } else {
                        alert(response.msg);
                    }
                    loading.hide();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    loading.hide();
                    alert(settings.errorMsg);
                }
            });
        }

        function confirmSubmit(isSetupFast) {
            var loading = $('#form-confirm-loading');
            $.ajax({
                url: settings.url,
                type: 'POST',
                dataType: 'json',
                data: {
                    'process' : 'confirm',
                },
                success: function (response, textStatus, errorThrown) {
                    if (response.result == 'success') {
                        if(!isSetupFast){
                            $('#confirm-content').hide();

                        }else{
                            showScreen(MIGRATION);
                            $('#support').hide();
                            $('#migration').removeClass('col-md-9');
                            $('#migration').addClass('col-md-12');

                        }
                        $('#import-content').html(response.html);
                        $('#import-content').show();
                        setActiveProgress(MIGRATION);
                        setDoneProgress(CONFIGURATION);
                        setTimeout(clearData, settings.delay);
                    } else {
                        alert(response.msg);
                    }
                    if(loading.is(":visible")){
                        loading.hide();
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    if(loading.is(":visible")){

                        loading.hide();
                    }
                    alert(settings.errorMsg);
                }
            });
        }

        function resumeSubmit() {
            var loading = $('#form-resume-loading'),
                formResume = $('#form-resume');
            var data = formResume.serialize();
            loading.show();
            $.ajax({
                url: settings.url,
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function (response, textStatus, errorThrown) {
                    loading.hide();
                    if (response.result == 'success') {
                        $('#import-content').html(response.html);
                        $('#resume-content').hide();
                        $('#setup-content').hide();
                        $('#import-content').show();
                        $('#support').hide();
                        setDoneProgress(SETUP);
                        setDoneProgress(CONFIGURATION);
                        setActiveProgress(MIGRATION);
                        if (settings.resume_process == 'clear') {
                            setTimeout(clearData, settings.delay);
                        } else {
                            setTimeout(function () {
                                importData(settings.resume_type);
                            }, settings.delay);
                        }
                    } else {
                        alert(response.msg);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    loading.hide();
                    alert(settings.errorMsg);
                }
            });
        }

        function showSupport(){
            $('#support').show();
            if($('#migration').hasClass('col-md-12')){
                $('#migration').removeClass('col-md-12');
            }
            if(!$('#migration').hasClass('col-md-9')){
                $('#migration').addClass('col-md-9');
            }
        }
        function hideSupport(){
            $('#support').hide();
            if($('#migration').hasClass('col-md-9')){
                $('#migration').removeClass('col-md-9');
            }
            if(!$('#migration').hasClass('col-md-12')){
                $('#migration').addClass('col-md-12');
            }
        }
    }
});

