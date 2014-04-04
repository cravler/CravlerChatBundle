'use strict';

function AppCtrl($rootScope, $scope, chat, notification) {

    var getMention = function(message) {
        var text, pattern, mention;
        text = message;
        pattern = /\B\@([\w\-]+)/gim;
        mention = text.match(pattern);

        if(mention) {
            return String(mention).split("@")[1];
        }

        return false;
    };

    var scrollDown = function() {
        setTimeout(function() {
            $('#chat-messages').animate({ scrollTop: $('#chat-messages-inner').height() }, 1000);
            $('#chat-messages').getNiceScroll().resize();
        }, 50);
    };

    $scope.messages = [];

    $scope.mention = function (name) {
        $scope.message = '@' + name + ' ';
        $('#msg-box').focus();
    };

    $scope.changeName = function() {
        chat.changeName($scope.newName, function(result) {
            if (!result) {
                alert('There was an error changing your name');
            } else {
                $scope.name = $scope.newName;
                $scope.newName = '';
                $('#changeNameModal').modal('hide');
            }
        });
    };

    $scope.sendMessage = function() {
        chat.sendMessage($scope.message, function() {
            // clear message box
            var mention = getMention($scope.message);
            if (mention) {
                $scope.message = '@' + mention + ' ';
            } else {
                $scope.message = '';
            }
        });
    };

    notification.on('cravler_chat.chat.message::added', function(obj) {
        var mention, me;
        var message = obj.data;
        mention = getMention(message.text);
        $scope.messages.push({
            user: message.user,
            text: message.text,
            me: 'show',
            cls: mention ? 'alert-info' : '',
            icon: mention ? 'fa-lock' : 'fa-user'
        });

        scrollDown();
    });

    notification.on('cravler_chat.chat.user::name_changed', function(obj) {
        var i;
        for (i = 0; i < $scope.users.length; i++) {
            if ($scope.users[i] === obj.data.oldName) {
                $scope.users[i] = obj.data.newName;
            }
        }

        $scope.messages.push({
            system: true,
            text: 'User ' + obj.data.oldName + ' is now known as ' + obj.data.newName + '.',
            me: 'offline al show'
        });

        scrollDown();
    });

    notification.on('cravler_chat.chat.user::join', function(obj) {
        $scope.messages.push({
            system: true,
            text: 'User ' + obj.data.name + ' has joined.',
            me: 'offline al show'
        });

        if ($scope.name !== obj.data.name && $scope.users.indexOf(obj.data.name) === -1) {
            $scope.users.push(obj.data.name);
        }

        scrollDown();
    });

    notification.on('cravler_chat.chat.user::left', function(obj) {
        $scope.messages.push({
            system: true,
            text: 'User ' + obj.data.name + ' has left.',
            me: 'offline al show'
        });

        var i, user;
        for (i = 0; i < $scope.users.length; i++) {
            user = $scope.users[i];
            if (user === obj.data.name) {
                $scope.users.splice(i, 1);
                break;
            }
        }

        scrollDown();
    });

    var init_done = false;
    notification.on('cravler_chat.chat.user::init', function(obj) {
        if (!init_done) {
            init_done = true;
            $scope.name = obj.data.name;
            $scope.users = obj.data.users;
        }
    });

    $(function() {
//        $('#changeNameModal').modal('show');

        $('#chat-messages').niceScroll({
            zindex: 1060
        });

        $('.go-full-screen').click(function() {
            var icon = $(this).find('i');
            var backdrop = $('.white-backdrop');
            var wbox = $(this).parents('.widget-box');

            if(wbox.hasClass('widget-full-screen')) {
                backdrop.fadeIn(200,function() {
                    wbox.removeClass('widget-full-screen',function() {
                        icon.removeClass('fa-compress');
                        icon.addClass('fa-expand');
                        backdrop.fadeOut(200);
                    });
                });
            } else {
                backdrop.fadeIn(200,function() {
                    wbox.addClass('widget-full-screen',function() {
                        icon.removeClass('fa-expand');
                        icon.addClass('fa-compress');
                        backdrop.fadeOut(200);
                    });
                });
            }
        });
    });
}
