(function (window, document, $, gtsAPIConfig) {
    var gtsAPI = gtsAPI || {};
    gtsAPIConfig.callbacksObjectTemplate = function () {
        return {
            // return false to prevent send data
            before: [],
            response: {
                success: [],
                error: []
            },
            ajax: {
                done: [],
                fail: [],
                always: []
            }
        }
    };
    gtsAPI.setup = function () {
        // selectors & $objects
        this.actionName = 'gtsapi_action';
        this.action = ':submit[name=' + this.actionName + ']';
        this.form = '.gtsAPI_form';
        this.$doc = $(document);
        
        this.sendData = {
            $form: null,
            action: null,
            formData: null
        };
        // this.$sendRow = null;
        this.timeout = 300;
    };
    gtsAPI.initialize = function () {
        gtsAPI.setup();
                        
        gtsAPI.Order.initialize();
    };
    // gtsAPI.controller = function () {
    //     var self = this;
    //     //console.info(self.sendData.action);
    //     switch (self.sendData.action) {
    //         // case 'report/update':
    //         //     gtsAPI.Order.update();
    //         //     break;
    //         // case 'report/update_file':
    //         //     gtsAPI.Order.update_file();
    //         //     break;
    //         default:
    //             return;
    //     }
    // };
    gtsAPI.send = function (data, callbacks) {
        var runCallback = function (callback, bind) {
            if (typeof callback == 'function') {
                return callback.apply(bind, Array.prototype.slice.call(arguments, 2));
            }
            else if (typeof callback == 'object') {
                for (var i in callback) {
                    if (callback.hasOwnProperty(i)) {
                        var response = callback[i].apply(bind, Array.prototype.slice.call(arguments, 2));
                        if (response === false) {
                            return false;
                        }
                    }
                }
            }
            return true;
        };

        var url = (gtsAPIConfig.actionUrl)
                      ? gtsAPIConfig.actionUrl
                      : document.location.href;
        // send
        var xhr = function (callbacks) {
            return $.post(url, data, function (response) {
                if (response.success) {
                    if (response.message) {
                        getTables.Message.success(response.message);
                    }
                    //console.info(callbacks.response.success);
                    runCallback(callbacks.response.success, gtsAPI, response);
                }
                else {
                    getTables.Message.error(response.message);
                    runCallback(callbacks.response.error, gtsAPI, response);
                }
            }, 'json')
        }(callbacks);
    };
    

    gtsAPI.Order = {
        callbacks: {
            check: gtsAPIConfig.callbacksObjectTemplate(),
        },
        setup: function () {
        },
        initialize: function () {
            // $(document).on('click','.gtsapi-reys-check', function(e) {
                // e.preventDefault();
                // gtsAPI.$sendRow = $(this);
                // $('.get-table.TPReys .get-table-first').each(function(){
                    // if($(this).data("action") == "getTable/create"){
                        // getTables.$doc.on('shown.bs.modal', function (event) {
                            // if(gtsAPI.$sendRow){
                                // $tr = gtsAPI.$sendRow.closest(".get-table-tr");
                                // var row = {};
                                // $tr.find("td").each(function(){
                                    // row[$(this).data("name")] = $(this).data("value");
                                // });
                                // $(".gts_modal #tp_reys_plan_id").val(row["tp_reys_plan_id"]);
                                // $(".gts_modal #point_a").val(row["point_a"]);
                                // $(".gts_modal #point_b").val(row["point_b"]);
                                // $('.gts_modal [name="type_auto_id"]').val(row["type_auto_id"]);
                                // $(".gts_modal #rashod").val(row["rashod"]);
                                // $(".gts_modal #in_nds").val(row["in_nds"]);
                                // $(".gts_modal #add_price_procent").val(row["add_price_procent"]);
                                // $(".gts_modal #sum").val(row["sum"]);
                                // gtsAPI.$sendRow = null;
                                // getTables.$doc.off('shown.bs.modal');
                            // }
                        // });
                        // $(this).trigger("click");
                    // }
                // });
                // var callbacks = gtsAPI.Order.callbacks;
                // callbacks.check.response.success = function (response) {
                    
                // };
                
                // gtsAPI.send(gtsAPI.sendData.formData, gtsAPI.Order.callbacks.check);
            // });
        },
    };
    
    $(document).ready(function ($) {
        gtsAPI.initialize();
    });

    window.gtsAPI = gtsAPI;
})(window, document, jQuery, gtsAPIConfig);