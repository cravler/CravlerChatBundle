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

    app.factory('notification', function ($rootScope) {
        return {
            on: function(ename, cb) {
                CravlerRemote.onMessage(ename, wrapper($rootScope, cb));
            }
        };
    });

    app.factory('chat', function ($rootScope) {
        var prefix = 'CravlerChat_Chat.';
        return {
            changeName: function(name, cb) {
                CravlerRemote.invoke(prefix + 'changeName', name, wrapper($rootScope, cb));
            },
            sendMessage: function(message, cb) {
                CravlerRemote.invoke(prefix + 'sendMessage', message, wrapper($rootScope, cb));
            }
        };
    });

})(app);
