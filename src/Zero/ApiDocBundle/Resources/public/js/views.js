ApiDoc.module('views', function(views, ApiDoc, Backbone, Marionette, $, _) {
    var unflattenDict = function(body) {
        var found = true, oKey, dictMatch, subKey;
        while(found) {
            found = false;

            _.each(body, function(value, key) {
                dictMatch = key.match(/^(.+)\[([^\]]+)\]$/);

                if(dictMatch) {
                    found = true;
                    oKey = dictMatch[1];
                    subKey = dictMatch[2];
                    body[oKey] = body[oKey] || {};
                    body[oKey][subKey] = value;
                    delete body[key];
                } else {
                    body[key] = value;
                }
            });
        }
        return body;
    };

    views.MethodNode = Marionette.ItemView.extend({
        template: '#template-method-node',
        tagName: 'li',
        events: {
            'click a': 'onClick'
        },

        modelEvent: {
            'change:isVisible': 'onVisibilityChange'
        },

        onClick: function() {
            var activeMethods = ApiDoc.activeMethods;

            if(!activeMethods.get(this.model)) {
                activeMethods.unshift(this.model);
            }

            this.model.set('isActive', true);
        },

        onVisibilityChange: function() {
            if(this.model.get('isVisible')) {
                this.$el.removeClass('hidden');
            } else {
                this.$el.addClass('hidden');
            }
        }
    });

    views.TreeNode = Marionette.CompositeView.extend({
        template: '#template-tree-node',
        tagName: 'li',
        className: 'parent_li',
        childViewContainer: '.children',
        ui: {
            countBadge: '.badge',
            children: '.children'
        },
        collectionEvents: {
            'change:isVisible': 'onVisibilityChange'
        },
        events: {
            'keydown': 'onKeyDown'
        },

        onKeyDown: function(e) {
            var $children = this.ui.children, handled = false;
            switch(e.key) {
                case 'Right':
                    handled = true;
                    $children.collapse('show');
                    break;
                case 'Left':
                    handled = true;
                    $children.collapse('hide');
                    break;
                default:
                    break;
            }

            if(handled) {
                e.preventDefault();
                e.stopPropagation();
            }
        },

        onVisibilityChange: function() {
            var hidden = this.collection.where({isVisible: false}).length;
            var currentVisible = this.collection.length - hidden;

            this.model.set('isVisible', currentVisible > 0);

            if(currentVisible > 0) {
                this.$el.removeClass('hidden');
            } else {
                this.$el.addClass('hidden');
            }

            this.ui.countBadge.text(currentVisible);

            if(currentVisible == 1) {
                this.ui.children.collapse('show');
            } else {
                this.ui.children.collapse('hide');
            }
        }
    });

    views.ResourceNode = views.TreeNode.extend({
        childView: views.MethodNode
    });

    views.SectionNode = views.TreeNode.extend({
        childView: views.ResourceNode,
        buildChildView: function(child, ChildViewClass, childViewOptions) {
            var options = _.extend({
                model: child,
                collection: child.get('children')
            }, childViewOptions);

            return new ChildViewClass(options);
        }
    });
    views.SectionNodes = Marionette.CompositeView.extend({
        template: '#template-tree',
        className: 'row',
        childViewContainer: '.children',
        childView: views.SectionNode,
        events: {
            'submit': 'onSearch'
        },
        ui: {
            search: 'input[name="search"]'
        },

        onSearch: function(e) {
            var searchValue = this.ui.search.val();
            e.preventDefault();

            ApiDoc.methods.each(function(method) {
                var isVisible = searchValue.length > 0 ? method.get('uri').indexOf(searchValue) >= 0 : true;
                //                if(method.get('isVisible') != isVisible) {
                //                    console.log('method', method.id, 'is ' + (isVisible ? '' : 'not ') + 'visible');
                //                }
                method.set('isVisible', isVisible);
            });
        },

        getChildView: function(model) {
            if(model instanceof ApiDoc.entities.Resource) {
                return views.ResourceNode;
            }

            return this.childView;
        },
        buildChildView: function(child, ChildViewClass, childViewOptions) {
            var options = _.extend({
                model: child,
                collection: child.get('children')
            }, childViewOptions);
            return new ChildViewClass(options);
        }
    });

    views.Settings = Marionette.ItemView.extend({
        template: '#template-settings',
        className: 'modal fade',
        attributes: {
            id: "modal-settings",
            tabindex: "-1",
            role: "dialog",
            ariaLabelledby: "modal-settings-label",
            ariaHidden: "true"
        },
        events: {
            'hide.bs.modal': function() {
                ApiDoc.cookies.set('settings', this.model.toJSON());
            }
        },

        onRender: function() {
            this.modelBinder = new Backbone.ModelBinder();
            this.modelBinder.bind(this.model, this.el);
        },

        onBeforeDestroy: function() {
            this.modelBinder.unbind();
            delete this.modelBinder;
        }
    });

    views.Tab = Marionette.ItemView.extend({
        template: '#template-tab',
        tagName: 'li',

        events: {
            'click [role="tab"]': 'onClick'
        },

        modelEvents: {
            'change isActive': 'onChangeIsActive',
            'change isOutside': 'onChangeIsOutside'
        },

        initialize: function() {
            this.onChangeIsActive();
        },

        onClick: function(e) {
            e.preventDefault();
            this.model.set('isActive', true);
        },

        onChangeIsActive: function() {
            var isActive = this.model.get('isActive');
            if(isActive) {
                this.$el.addClass('active');
            } else {
                this.$el.removeClass('active');
            }
        },

        onChangeIsOutside: function() {
            var isOutside = this.model.get('isOutside');
            if(isOutside) {
                this.$el.addClass('hidden');
            } else {
                this.$el.removeClass('hidden');
            }
        }
    });

    views.TabDropItem = Marionette.ItemView.extend({
        tagName: 'li',
        template: '#template-tab-drop-item',
        events: {
            'click [role="menuitem"]': 'onClick'
        },

        onClick: function(e) {
            e.preventDefault();
            this.model.set('isActive', true);
        }
    });

    views.TabDrop = Marionette.CompositeView.extend({
        tagName: 'li',
        className: 'dropdown pull-right tabdrop',
        template: '#template-tab-drop',
        childView: views.TabDropItem,
        childViewContainer: '.dropdown-menu',
        collectionEvents: {
            'add remove': 'onCollectionChange'
        },

        onCollectionChange: function() {
            if(this.collection.length > 0) {
                this.$el.removeClass('hidden');
            } else {
                this.$el.addClass('hidden');
            }
        }
    });

    views.Tabs = Marionette.CollectionView.extend({
        tagName: 'ul',
        className: 'nav nav-tabs nav-tabs-block',
        childView: views.Tab,
        events: {
            'click .close': 'onClickClose'
        },
        collectionEvents: {
            'change:isActive': 'onCollectionChange'
        },

        initialize: function() {
            this.outsideCollection = new Backbone.Collection();
        },

        onCollectionChange: function(model) {
            if(model.get('isActive')) {
                var currentSelected = this.collection.where({isActive: true});
                _.each(currentSelected, function(selected) {
                    if(selected != model) {
                        selected.set('isActive', false);
                    }
                });

                this.calculateOutside();
            }
        },

        onShow: function() {
            this.calculateOutside();
        },

        calculateOutside: function() {
            var outside = [], inside = [], currentWidth = 0, maxWidth = this.getWidth(), self = this, calculatedWidth = 0;

            // First we get the active tab
            var activeItem = this.collection.findWhere({isActive: true});
            if(activeItem) {
                currentWidth = this.getItemWidth(activeItem);
                inside.push(activeItem);
            }

            // Get all other tabs
            this.collection.each(function(model) {
                // Skip the active one
                if(!model.get('isActive')) {
                    calculatedWidth = currentWidth + self.getItemWidth(model);

                    if(calculatedWidth > maxWidth) {
                        outside.push(model);
                    } else {
                        currentWidth = calculatedWidth;
                        inside.push(model);
                    }
                }
            });

            _.each(inside, function(model) {
                model.set('isOutside', false);
                this.outsideCollection.remove(model);
            }, this);

            _.each(outside, function(model) {
                model.set('isOutside', true);
                this.outsideCollection.add(model);
            }, this);
        },

        getWidth: function() {
            var outerWidth = this.$el.width() - 10;
            var tabDropWidth = this.$el.find('.tabdrop').outerWidth();

            return outerWidth - tabDropWidth;
        },

        getItemWidth: function(item) {
            var view = this.children.findByModel(item);
            var hadHidden = view.$el.hasClass('hidden');
            view.$el.removeClass('hidden');

            var outerWidth = view.$el.outerWidth();

            if(hadHidden) {
                view.$el.addClass('hidden');
            }
            return outerWidth;
        },

        onRender: function() {
            this.showTabDropView();
        },

        showTabDropView: function() {
            if(!this._showingTabDropView) {
                this.triggerMethod('before:render:tabdrop');

                this._showingTabDropView = true;
                this.addTabDropView();

                this.triggerMethod('render:tabdrop');
            }
        },

        addTabDropView: function() {

            var TabDropView = views.TabDrop;

            // get the tabDropViewOptions
            var tabDropViewOptions = {
                model: new Backbone.Model(),
                collection: this.outsideCollection
            };

            // build the view
            var view = new TabDropView(tabDropViewOptions);

            // trigger the 'before:show' event on `view` if the collection view
            // has already been shown
            if(this._isShown) {
                this.triggerMethod.call(view, 'before:show');
            }

            // Store the `tabDropView` like a `childView` so we can properly
            // remove and/or close it later
            this.children.add(view);

            // Render it and show it
            this.renderChildView(view, -1);

            // call the 'show' method if the collection view
            // has already been shown
            if(this._isShown) {
                this.triggerMethod.call(view, 'show');
            }
        },

        onClickClose: function(e) {
            var $tab = $(e.target).parent();
            var id = $tab.attr('href').substr(1);

            var model = this.collection.get(id);
            var wasActive = model.get('isActive');
            this.collection.remove(model);

            if(wasActive) {
                // Deactivate removed model
                model.set('isActive', false);

                // Activate first in collection (if any)
                if(this.collection.length > 0) {
                    this.collection.at(0).set('isActive', true);
                }
            }
        }
    });

    views.KV = Marionette.ItemView.extend({
        template: '#template-kv',
        className: 'form-group form-inline',
        attributes: function() {
            return {
                id: this.model.cid
            };
        },

        onRender: function() {
            var bindings = {
                key: 'input.key',
                value: 'input.value'
            };
            this.modelBinder = new Backbone.ModelBinder();
            this.modelBinder.bind(this.model, this.el, bindings);
        },

        onBeforeDestroy: function() {
            this.modelBinder.unbind();
            delete this.modelBinder;
        }
    });

    views.KVStore = Marionette.CompositeView.extend({
        template: '#template-kvstore',
        childView: views.KV,
        childViewContainer: '.list',
        events: {
            'click .add': 'addKV',
            'click .remove': 'removeKV'
        },

        initialize: function(options) {
            this.disableAdd = options.disableAdd;
        },

        onShow: function() {
            if(this.disableAdd) {
                this.$el.find('.add').remove();
            }
        },

        addKV: function(e) {
            e.preventDefault();

            var kv = new this.collection.model;
            this.collection.add(kv);
        },

        removeKV: function(e) {
            e.preventDefault();

            var id = $(e.target).parent().attr('id');
            this.collection.remove(id);
        }
    });

    views.Sandbox = Marionette.LayoutView.extend({
        template: '#template-method-sandbox',
        tagName: 'form',
        events: {
            'submit': 'onSubmit',
            'click .set-content-type': 'onClickContentType',
            'change .content': 'onChangeContent',
            'click .req-to-expand': 'onClickReqExpand',
            'click .req-to-collapse': 'onClickReqCollapse',
            'click .res-to-expand': 'onClickResExpand',
            'click .res-to-collapse': 'onClickResCollapse',
            'click .to-raw': 'onClickRaw',
            'click .to-prettify': 'onClickPrettify'
        },
        regions: {
            requirementsRegion: '.requirements',
            filtersRegion: '.filters',
            parametersRegion: '.parameters',
            headersRegion: '.headers'
        },
        ui: {
            requestPane: '.request-panel',
            responsePane: '.response-panel',
            contentTypeValue: '.content-type-value',
            requestHeaders: '.request-headers',
            responseHeaders: '.response-headers',
            responseBody: '.response-body',
            content: '.content',
            toReqExpand: '.req-to-expand',
            toReqCollapse: '.req-to-collapse',
            toResExpand: '.res-to-expand',
            toResCollapse: '.res-to-collapse',
            toRaw: '.to-raw',
            toPrettify: '.to-prettify'
        },

        initialize: function() {
            this.listenTo(ApiDoc.settings, 'change:bodyFormat', function(model) {
                this.ui.contentTypeValue.val(model.get('bodyFormat'));
            });
        },

        onClickReqExpand: function() {
            this.ui.requestHeaders.removeClass('collapse');
            this.ui.toReqExpand.addClass('hidden');
            this.ui.toReqCollapse.removeClass('hidden');
        },

        onClickReqCollapse: function() {
            this.ui.requestHeaders.addClass('collapse');
            this.ui.toReqExpand.removeClass('hidden');
            this.ui.toReqCollapse.addClass('hidden');
        },

        onClickResExpand: function() {
            this.ui.responseHeaders.removeClass('collapse');
            this.ui.toResExpand.addClass('hidden');
            this.ui.toResCollapse.removeClass('hidden');
        },

        onClickResCollapse: function() {
            this.ui.responseHeaders.addClass('collapse');
            this.ui.toResExpand.removeClass('hidden');
            this.ui.toResCollapse.addClass('hidden');
        },

        onClickRaw: function() {
            this.ui.toPrettify.removeClass('hidden');
            this.ui.toRaw.addClass('hidden');

            this.renderRawBody();
        },

        onClickPrettify: function() {
            this.ui.toPrettify.addClass('hidden');
            this.ui.toRaw.removeClass('hidden');

            this.renderPrettifiedBody();
        },

        onSubmit: function(e) {
            var url = this.model.get('url');
            var endpoint = ApiDoc.settings.get('apiEndpoint');
            var method = this.model.get('formMethod');
            var uriParams = {}, headers = {}, body, self = this;
            var params = {}, missingRequirements = [];

            e.preventDefault();

            this.model.get('requirements').each(function(requirement) {
                var key = requirement.get('key');
                if(requirement.get('value') == '') {
                    missingRequirements.push(key);
                }
                uriParams[key] = requirement.get('value');
            });

            // set requestFormat
            //            if(ApiDoc.settings.get('requestFormatMethod') == 'format_param') {
            //                uriParams['_format'] = ApiDoc.settings.get('format');
            //            }

            if(!_.isEmpty(missingRequirements)) {
                alert('Missing URI requirements: ' + missingRequirements.join(', '));
                return false;
            }

            _.each(uriParams, function(value, key) {
                if(url.indexOf('{' + key + '}') !== -1) {
                    url = url.replace('{' + key + '}', value);
                }
            });

            // append the query authentication
            if(ApiDoc.settings.get('authDelivery') == 'query') {
                url += url.indexOf('?') > 0 ? '&' : '?';
                url += ApiDoc.settings.get('apiKeyParameter') + '=' + ApiDoc.settings.get('apiKey');
            }

            // get filters
            this.model.get('filters').each(function(filter) {
                if(filter.get('value').length) {
                    url += url.indexOf('?') > 0 ? '&' : '?';
                    url += filter.get('key') + '=' + filter.get('value');
                }
            });

            // get parameters
            this.model.get('parameters').each(function(parameter) {
                params[parameter.get('key')] = parameter.get('value');
            });

            // get headers
            if(ApiDoc.settings.get('requestFormatMethod') == 'accept_header') {
                headers['Accept'] = ApiDoc.settings.get('requestFormat');
            }

            if(ApiDoc.settings.get('authDelivery') == 'http_basic') {
                headers['Authorization'] = 'Basic ' + btoa(ApiDoc.settings.get('apiKey') + ':' + ApiDoc.settings.get('apiPass'));
            } else if(ApiDoc.settings.get('authDelivery') == 'header') {
                headers[ApiDoc.settings.get('apiKeyParameter')] = ApiDoc.settings.get('apiKey');
            }
            this.model.get('headers').each(function(header) {
                headers[header.get('key')] = header.get('value');
            });

            var contentType = typeof(headers['Content-Type']) != 'undefined' ? headers['Content-Type'] : null;

            // prepare final parameters
            if(this.model.get('content').length) {
                body = this.model.get('content');
            } else {
                if(contentType == 'application/json' && method != 'GET') {
                    body = unflattenDict(params);
                    body = JSON.stringify(body);
                } else {
                    body = params;
                }
            }

            if(body.length > 0 && !contentType) {
                alert('Trying to send body without a Content-Type header');
                return false;
            }

            this.$el.find('input, button').attr('disabled', 'disabled');

            // and trigger the API call
            $.ajax({
                url: endpoint + url,
                type: method,
                data: body,
                headers: headers,
                crossDomain: true,
                complete: function(xhr) {
                    self.setRequest(method, url, headers, body);
                    self.setResponseHeaders(xhr);
                    self.setResponseBody(xhr);

                    self.ui.requestPane.collapse('hide');
                    self.ui.responsePane.collapse('show');

                    self.$el.find('input:not(.keep-disabled), button').removeAttr('disabled');
                }
            });

            return false;
        },

        setRequest: function(method, url, headers, body) {
            var text = [method + ' ' + url, ''];
            _.each(headers, function(value, key) {
                text.push(key + ': ' + value);
            });
            text.push(' ');
            text.push(body);
            this.ui.requestHeaders.text(text.join('\n'));
        },

        setResponseHeaders: function(xhr) {
            var text = xhr.status + ' ' + xhr.statusText + "\n\n";
            text += xhr.getAllResponseHeaders();

            this.ui.responseHeaders.text(text);
        },

        setResponseBody: function(xhr) {
            var text = xhr.responseText;

            this.ui.responseBody.data('raw-response', text);

            this.renderPrettifiedBody();
        },

        renderPrettifiedBody: function() {
            var responseBody = this.ui.responseBody;
            var raw = responseBody.data('raw-response');

            try {
                var data = typeof raw === 'string' ? JSON.parse(raw) : raw;
                raw = JSON.stringify(data, undefined, '  ');
            } catch(err) {
            }

            // HTML encode the result
            var prettyPrinted = $('<div>').text(raw).html();

            responseBody.removeClass('prettyprinted');
            responseBody.html(prettyPrinted);
            prettyPrint && prettyPrint();
        },

        renderRawBody: function() {
            var responseBody = this.ui.responseBody;
            var raw = responseBody.data('raw-response');

            responseBody.addClass('prettyprinted');
            responseBody.text(raw);
        },

        onChangeContent: function() {
            this.model.set('content', this.ui.content.val(), {trigger: true});
        },

        onClickContentType: function(e) {
            var headers = this.model.get('headers');
            headers.set({
                id: 'content-type',
                key: 'Content-Type',
                value: this.ui.contentTypeValue.val()
            });
        },

        onShow: function() {
            this.ui.contentTypeValue.val(ApiDoc.settings.get('bodyFormat'));

            this.requirementsRegion.show(new views.KVStore({
                collection: this.model.get('requirements'),
                disableAdd: true,
                disableEdit: true
            }));

            this.filtersRegion.show(new views.KVStore({
                collection: this.model.get('filters')
            }));

            this.parametersRegion.show(new views.KVStore({
                collection: this.model.get('parameters')
            }));

            this.headersRegion.show(new views.KVStore({
                collection: this.model.get('headers')
            }));
        }
    });

    views.Method = Marionette.LayoutView.extend({
        className: 'tab-pane',
        attributes: function() {
            return {
                id: this.model.get('id')
            };
        },
        modelEvents: {
            'change isActive': 'onChangeIsActive'
        },

        regions: {
            sandboxRegion: '.sandbox'
        },

        initialize: function() {
            var sandbox = {
                id: this.model.get('id'),
                method: this.model.get('method'),
                uri: this.model.get('uri'),
                host: this.model.get('host'),
                https: this.model.get('https')
            };

            var requirements = [], filters = [], parameters = [], headers = [];

            _.each(this.model.get('requirements'), function(data, name) {
                var model = {
                    id: name.toLowerCase(),
                    key: name,
                    remove: false,
                    edit: false
                };

                if(data.description) {
                    model.description = data.description;
                }

                requirements.push(model);
            });

            _.each(this.model.get('filters'), function(data, name) {
                var model = {
                    id: name.toLowerCase(),
                    key: name,
                    remove: false,
                    edit: false
                };

                if(data.description) {
                    model.description = data.description;
                }

                filters.push(model);
            });

            _.each(this.model.get('parameters'), function(data, name) {
                var model = {
                    id: name.toLowerCase(),
                    key: name,
                    remove: false,
                    edit: false
                };

                if(data.dataType) {
                    model.description = '[' + data.dataType + ']';
                }
                if(data.format) {
                    model.description = data.format;
                }
                if(data.description) {
                    model.description = data.description;
                }
                if(data.default) {
                    model.value = data.default;
                }

                parameters.push(model);
            });

            if(ApiDoc.settings.has('acceptType')) {
                headers.push({
                    id: 'accept',
                    key: 'Accept',
                    value: ApiDoc.settings.get('acceptType'),
                    remove: false
                });
            }

            _.each(this.model.get('headers'), function(data, name) {
                var model = {
                    id: name.toLowerCase(),
                    key: name
                };

                headers.push(model);
            });

            sandbox.requirements = requirements;
            sandbox.filters = filters;
            sandbox.parameters = parameters;
            sandbox.headers = headers;

            this.model.get('sandbox').set(sandbox, {merge: true, add: true, remove: false});

            this.onChangeIsActive();
        },

        onShow: function() {
            this.sandboxRegion.show(new views.Sandbox({
                model: this.model.get('sandbox')
            }));
        },

        onChangeIsActive: function() {
            var isActive = this.model.get('isActive');
            if(isActive) {
                this.$el.addClass('active');
            } else {
                this.$el.removeClass('active');
            }
        }
    });

    views.TabsContainer = Marionette.CollectionView.extend({
        childView: views.Method,
        className: 'tab-content',
        buildChildView: function(child, ChildViewClass, childViewOptions) {
            var options = _.extend({
                model: child
            }, childViewOptions);
            var ChildView = ChildViewClass.extend({
                template: '#template-' + child.id
            });
            return new ChildView(options);
        }
    });

    views.addInitializer(function() {
        var sectionNodes = new views.SectionNodes({
            collection: ApiDoc.sections
        });
        ApiDoc.sidebar.show(sectionNodes);

        var tabs = new views.Tabs({
            collection: ApiDoc.activeMethods
        });
        ApiDoc.tabs.show(tabs);

        var tabsContainer = new views.TabsContainer({
            collection: ApiDoc.activeMethods
        });
        ApiDoc.tabsContainer.show(tabsContainer);

        ApiDoc.modalSettings.show(new views.Settings({
            model: ApiDoc.settings
        }));
    });
});