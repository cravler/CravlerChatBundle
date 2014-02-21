(function(app) {
    'use strict';

    var wrapper = function($rootScope, cb) {
        return function() {
            var args = arguments;
            $rootScope.$apply(function() {
                if (cb && typeof cb === 'function') {
                    cb.apply(cb, args);
                }
            });
        };
    };

    var call = function(ename, args) {
        CravlerRemote.endpointsReady(function() {
            if (CravlerRemote.endpointExists(ename)) {
                var endpoint = jUtil.ns(ename);
                endpoint.apply(endpoint, args);
            } else {
                console.error('Endpoint "' + ename + '" not defined!');
            }
        });
    };

    app.factory('notification', function ($rootScope) {
        return {
            on: function(ename, cb) {
                CravlerRemote.onMessage(ename, wrapper($rootScope, cb));
            }
        };
    });

    app.factory('chat', function ($rootScope) {
        var prefix = 'Endpoints.CravlerChat_Chat.';
        return {
            changeName: function(name, cb) {
                call(prefix + 'changeName', [name, wrapper($rootScope, cb)]);
            },
            sendMessage: function(message, cb) {
                call(prefix + 'sendMessage', [message, wrapper($rootScope, cb)]);
            }
        };
    });

})(app);
