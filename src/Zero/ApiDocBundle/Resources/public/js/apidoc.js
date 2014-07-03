(function(global) {

    var ApiDoc = global.ApiDoc = new Backbone.Marionette.Application();

    ApiDoc.addRegions({
        sidebar: '.sidebar',
        tabs: '.tabs',
        tabsContainer: '.tabs-container',
        modalSettings: '.modal-settings'
    });

    ApiDoc.cookies = {
        get: function(cookieName, defaultValue) {
            var cookieData = $.cookie(cookieName), data;
            if(!cookieData) {
                this.set(cookieName, defaultValue);
                data = defaultValue;
            } else {
                data = JSON.parse(cookieData);
            }

            return data;
        },

        set: function(cookieName, value) {
            $.cookie(cookieName, JSON.stringify(value));

            return this;
        }
    };

    ApiDoc.on('before:start', function(options) {
        var sections = [], section, resources, resource, methods = [], method;

        if(!_.isUndefined(options)) {

            if(_.isObject(options.sections)) {

                _.each(options.sections, function(resourceList, sectionName) {
                    resources = [];
                    _.each(resourceList, function(methodsList, resourceName) {

                        resource = {
                            type: 'resource',
                            id: _.uniqueId('resource_'),
                            name: resourceName,
                            children: []
                        };

                        _.each(methodsList, function(methodData) {
                            methodData['sandbox'] = {};
                            method = new ApiDoc.entities.Method(methodData);
                            resource.children.push(method);
                            methods.push(method);
                        });

                        if(sectionName == '_others') {
                            sections.push(resource);
                        } else {
                            resources.push(resource);
                        }
                    });

                    section = {
                        type: 'section',
                        id: _.uniqueId('section_'),
                        name: sectionName,
                        children: resources
                    };

                    if(sectionName != '_others') {
                        sections.push(section);
                    }
                });
                delete options.sections;
            }
        }

        options.sections = sections;
        options.methods = methods;

        ApiDoc.options = options;
    });

    ApiDoc.addInitializer(function(options) {
        var auth, settings = {
            apiEndpoint: options.endpoint,
            requestFormatMethod: options.requestFormatMethod
        };

        if(options.acceptType) {
            settings.acceptType = options.acceptType;
        }

        if(options.authentication) {
            auth = options.authentication;
            if(auth.delivery == 'http_basic') {
                settings.authDelivery = auth.delivery;
            } else if(auth.delivery == 'query') {
                settings.authDelivery = auth.delivery;
                settings.apiKeyParameter = auth.name;
                var search = window.location.search;
                var apiKeyStart = search.indexOf(auth.name) + auth.name.length + 1;
                if(apiKeyStart > 0) {
                    var apiKeyEnd = search.indexOf('&', apiKeyStart);
                    settings.apiKey = -1 == apiKeyEnd ? search.substr(apiKeyStart) : search.substring(apiKeyStart, apiKeyEnd);
                }
            } else if(auth.delivery == 'header') {
                settings.authDelivery = auth.delivery;
                settings.apiKeyParameter = auth.name;
            }
        }

        ApiDoc.settings = new ApiDoc.entities.Settings(settings);

        var storedSettings = ApiDoc.cookies.get('settings', {});
        ApiDoc.settings.set(storedSettings);
    });

    ApiDoc.addInitializer(function(options) {
        ApiDoc.sections = new ApiDoc.entities.Sections(options.sections);
    });

    ApiDoc.addInitializer(function(options) {
        ApiDoc.methods = new ApiDoc.entities.Methods(options.methods);
    });

    ApiDoc.addInitializer(function() {
        var previousMethods = ApiDoc.cookies.get('active_methods', {});
        var activeMethods = [];
        _.each(previousMethods, function(data, id) {
            var model = ApiDoc.methods.get(id);
            if(model) {
                if(data.active) {
                    model.set('isActive', true);
                }
                if(data.sandbox) {
                    model.get('sandbox').set(data.sandbox);
                }
                activeMethods.push(model);
            }
        });

        ApiDoc.activeMethods = new ApiDoc.entities.ActiveMethods();
        ApiDoc.listenTo(ApiDoc.activeMethods, 'add', function(model) {
            var activeMethods = ApiDoc.cookies.get('active_methods', {});
            var activeMethod = {active: model.get('isActive'), sandbox: model.get('sandbox').toJSON()};
            delete activeMethod.sandbox.id;
            activeMethods[model.id] = activeMethod;
            ApiDoc.cookies.set('active_methods', activeMethods);
        });

        ApiDoc.listenTo(ApiDoc.activeMethods, 'remove', function(model) {
            var activeMethods = ApiDoc.cookies.get('active_methods', {});
            delete activeMethods[model.id];
            ApiDoc.cookies.set('active_methods', activeMethods);
        });

        ApiDoc.listenTo(ApiDoc.activeMethods, 'change:isActive update', function(model) {
            var activeMethods = ApiDoc.cookies.get('active_methods', {});
            var activeMethod = {active: model.get('isActive'), sandbox: model.get('sandbox').toJSON()};
            delete activeMethod.sandbox.id;
            activeMethods[model.id] = activeMethod;
            ApiDoc.cookies.set('active_methods', activeMethods);
        });

        ApiDoc.activeMethods.set(activeMethods);
    });
})(window);