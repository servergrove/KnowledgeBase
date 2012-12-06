(function (angular, $, marked, CodeMirror) {
    'use strict';

    var window = this;

    Array.prototype.remove = function (element) {
        var position;
        if (-1 !== (position = this.indexOf(element))) {
            this.splice(position, 1);
        }
    };

    Array.prototype.add = function (elements) {
        angular.forEach(elements, function (element) {
            if (angular.isString(element)) {
                this.push(element);
            }
        }, this);
    };

    (function (module) {
        var configFunction = function ($httpProvider) {
            $httpProvider.defaults.headers.post = {'Content-Type': 'application/x-www-form-urlencoded'};
        };
        configFunction.$inject = ['$httpProvider'];
        module.config(configFunction);

        module.directive('ngEnterPress', function () {
            return {
                restrict: 'A',
                link: function (scope, element, attrs) {
                    $(element).keypress(function (e) {
                        if (e.which === 13) {
                            if (angular.isDefined(attrs.ngEnterPress)) {
                                scope.$apply(attrs.ngEnterPress);
                            }

                            e.preventDefault();
                        }
                    });
                }
            };
        });

        module.directive('deleteForm', function () {
            return {
                restrict: 'E',
                templateUrl: 'partials/delete-form.html',
                transclude: true,
                scope: {
                    button: '@button',
                    action: '@action',
                    method: '@method',
                    confirmation: '@confirmation'
                },
                link: function (scope, element, attrs) {
                    scope.doDelete = function () {
                        if (window.confirm(scope.confirmation)) {
                            element.find('form').submit();
                        }
                    };
                }
            };
        });

        module.factory('galleryModal', function () {
            var GalleryModal = function () {
                this.modal = $('#galleryModal');
            };

            angular.extend(GalleryModal.prototype, {
                modal: null,
                contentType: null,
                callback: null,

                show: function () {
                    this.modal.modal('show');

                    return this;
                },

                hide: function () {
                    this.modal.modal('hide');

                    return this;
                },

                setCallback: function (callback) {
                    this.callback = callback;

                    return this;
                },

                getCallback: function () {
                    return this.callback;
                },

                setContentType: function (contentType) {
                    this.contentType = contentType;

                    return this;
                },

                getContentType: function () {
                    return this.contentType;
                }
            });

            return new GalleryModal();
        });
    }(angular.module('ServerGroveKbAdmin', [])));

    this.getContentTypeWidgetController = function (contentType) {
        var controller = function ($scope) {
            $scope.contentType = contentType;

            $scope.setContentType = function (contentType) {
                $scope.$parent.contentType = $scope.contentType = contentType;
            };
        };

        controller.$inject = ['$scope'];

        return controller;
    };

    this.getActiveWidgetController = function (active) {
        var controller = function ($scope) {
            $scope.active = active;

            $scope.setActive = function (active) {
                $scope.active = active;
            };

            $scope.getClassIfActive = function (activeClass) {
                return $scope.active ? activeClass : '';
            };

            $scope.getClassIfDisabled = function (disabledClass) {
                return !$scope.active ? disabledClass : '';
            };
        };

        controller.$inject = ['$scope'];

        return controller;
    };

    this.getTranslationController = function (content, contentType, locale) {

        var textArea = $('textarea#translation_' + locale + '_content').hide(),

            controller = function ($scope, galleryModal) {
                var myCodeMirror = {};

                $scope.content = content;
                $scope.contentType = contentType;

                $scope.$watch('contentType', function (newValue, oldValue) {
                    if (angular.isDefined(myCodeMirror[oldValue])) {
                        myCodeMirror[oldValue].save();
                        myCodeMirror[oldValue].toTextArea();
                        delete myCodeMirror[oldValue];
                    }

                    if (angular.isUndefined(myCodeMirror[newValue])) {
                        var textarea = textArea.get(0);
                        myCodeMirror[newValue] = CodeMirror.fromTextArea(textarea, {
                            mode: 'wysiwyg' === newValue ? 'htmlmixed' : 'markdown',
                            lineNumbers: true,
                            lineWrapping: true,
                            onChange: function (instance) {
                                $scope.content = instance.getValue();
                                $scope.$apply();
                                $(textarea).trigger('change');
                            }
                        });

                        myCodeMirror[newValue].setSize('100%', 450);
                    }
                });

                $scope.getPreviewContent = function () {
                    switch ($scope.contentType) {
                    case 'wysiwyg':
                        return $scope.getHtmlContent();
                    case 'markdown':
                        return $scope.getMarkdownContent();
                    default:
                        return 'Undefined preview for this content type';
                    }
                };

                $scope.getMarkdownContent = function () {
                    return marked($scope.content);
                };

                $scope.getHtmlContent = function () {
                    return $scope.content;
                };

                $scope.openGallery = function () {
                    galleryModal.setContentType($scope.contentType).setCallback(function (html) {
                        if (angular.isDefined(myCodeMirror[$scope.contentType])) {
                            myCodeMirror[$scope.contentType].setValue($scope.content + "\n" + html);
                        }
                    }).show();
                };
            };
        controller.$inject = ['$scope', 'galleryModal'];

        return controller;
    };

    this.ArticleKeywordsCtrl = function ($scope, $http) {
        $scope.url = '';
        $scope.keywords = [];
        $scope.newKeywords = [];

        var sync = function () {
            $http.post($scope.url, $.param({keywords: $scope.keywords}));
        };

        $scope.addKeywords = function () {
            $scope.keywords.add($scope.newKeywords);
            $scope.newKeywords = [];
            sync();
        };

        $scope.removeKeyword = function (keyword) {
            $scope.keywords.remove(keyword);
            sync();
        };
    };

    this.ImageGalleryCtrl = function ($scope, $http, galleryModal) {
        $scope.images = {};
        $scope.url = '';
        $scope.selectedImage = null;
        $scope.width = '';
        $scope.height = '';
        $scope.displayPreview = false;

        $scope.$watch('url', function (newValue) {
            if ('' !== newValue) {
                $scope.fetch();
            }
        });

        $scope.$watch('selectedImage', function (newValue, oldValue) {
            if (null !== newValue) {
                newValue["class"] = "selected";
            }

            if (null !== oldValue) {
                oldValue["class"] = "";
            }
        });

        $scope.select = function (image) {
            $scope.selectedImage = image;
        };

        $scope.getPreviewStyle = function () {
            return 'width:' + $scope.width + ';height:' + $scope.height + ';';
        };

        $scope.togglePreview = function () {
            $scope.displayPreview = !$scope.displayPreview;
        };

        $scope.fetch = function () {
            $http.get($scope.url).success(function (data) {
                $scope.images = data;
            });
        };

        $scope.build = function () {
            switch (galleryModal.getContentType()) {
            case 'wysiwyg':
                var img = $(new Image()).attr({src: $scope.selectedImage.path});

                if ($scope.width.length > 0) {
                    img.attr({width: $scope.width});
                }

                if ($scope.height.length > 0) {
                    img.attr({height: $scope.height});
                }

                galleryModal.getCallback().call(this, $('<div>').append(img).html());
                break;
            case 'markdown':
                galleryModal.getCallback().call(this, '![' + $scope.selectedImage.path + '](' + $scope.selectedImage.path + ')');
                break;
            }

            $scope.selectedImage = null;
        };
    };

    this.NewArticleCtrl = function ($scope, $http) {
        $scope.title = { value: null, message: null, invalid: true };
        $scope.categories = { values: [], message: null, invalid: false };
        $scope.check_url = null;
        $scope.validate = false;

        var timeout = null, state, nextCall;

        $scope.$watch('categories.values', function (newValue, oldValue) {
            $scope.categories.invalid = 0 === $scope.categories.values.length;
        });

        $scope.$watch('title.value', function (newValue, oldValue) {

            if ('' === newValue || null === newValue) {
                window.clearTimeout(timeout);
                $scope.title.invalid = true;
                $scope.title.message = null;

                return;
            }

            var callback = function () {
                window.clearTimeout(timeout);
                nextCall = null;
                state = 'running';

                var request = $http.post($scope.check_url, $.param({title: newValue}));

                request.success(function (data) {
                    state = 'idle';
                    $scope.title.invalid = !data.result;
                    $scope.title.message = data.message;

                    if (angular.isFunction(nextCall)) {
                        nextCall.call(this);
                    }
                });
            };

            if ('running' !== state) {
                window.clearTimeout(timeout);
                timeout = window.setTimeout(callback, 200);
                nextCall = null;
            } else {
                nextCall = callback;
            }

        });

        $scope.isOptionSelected = function (option) {
            return -1 !== $scope.categories.values.indexOf(option);
        };

        $scope.getClassForControlGroup = function (field) {
            if (angular.isString($scope[field].message) && '' !== $.trim($scope[field].message)) {
                if (angular.isDefined($scope[field]) && $scope[field].invalid) {
                    return 'error';
                }

                return 'success';
            }

            return '';
        };
    };

    this.ArticleKeywordsCtrl.$inject = ['$scope', '$http'];
    this.ImageGalleryCtrl.$inject = ['$scope', '$http', 'galleryModal'];
    this.getActiveWidgetController.$inject = [];
    this.getContentTypeWidgetController.$inject = [];
    this.getTranslationController.$inject = [];
    this.NewArticleCtrl.$inject = ['$scope', '$http'];
}.call(window, window.angular, window.jQuery, window.marked, window.CodeMirror));
