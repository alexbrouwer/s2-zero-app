ApiDoc.module('entities', function(entities, ApiDoc, Backbone, Marionette, $, _) {

    entities.Settings = Backbone.Model.extend({
        defaults: {
            bodyFormat: '',
            requestFormat: '',
            format: '',
            apiKey: '',
            apiPass: '',
            apiEndpoint: '',
            authDelivery: null
        }
    });

    entities.KV = Backbone.Model.extend({
        defaults: {
            key: '',
            value: '',
            remove: true,
            edit: true
        }
    });

    entities.KVStore = Backbone.Collection.extend({
        model: entities.KV
    });

    entities.Sandbox = Backbone.AssociatedModel.extend({
        defaults: {
            method: 'get',
            uri: '',
            https: false,
            host: null,
            content: ''
        },
        relations: [
            {
                type: Backbone.Many,
                key: 'requirements',
                relatedModel: entities.KV,
                collectionType: entities.KVStore
            },
            {
                type: Backbone.Many,
                key: 'filters',
                relatedModel: entities.KV,
                collectionType: entities.KVStore
            },
            {
                type: Backbone.Many,
                key: 'parameters',
                relatedModel: entities.KV,
                collectionType: entities.KVStore
            },
            {
                type: Backbone.Many,
                key: 'headers',
                relatedModel: entities.KV,
                collectionType: entities.KVStore
            }
        ],
        initialize: function() {
            this.computedFields = new Backbone.ComputedFields(this);
        },
        computed: {
            formMethod: {
                depends: ['method'],
                get: function(fields) {
                    var method = fields.method.toUpperCase();
                    if(method == 'ANY') {
                        method = 'POST';
                    } else if(method.indexOf('|') !== -1) {
                        method = method.split('|').sort().pop();
                    }

                    return method;
                }
            },
            url: {
                depends: ['uri', 'host', 'https'],
                get: function(fields) {
                    var formAction = fields.uri;
                    if(fields.host) {
                        if(fields.https) {
                            formAction = 'https://' + formAction;
                        } else {
                            formAction = 'http://' + formAction;
                        }

                        formAction = fields.host + formAction;
                    }
                    return formAction;
                }
            }
        }
    });

    entities.Method = Backbone.AssociatedModel.extend({
        initialize: function() {
            this.computedFields = new Backbone.ComputedFields(this);
        },

        relations: [
            {
                type: Backbone.One,
                key: 'sandbox',
                relatedModel: entities.Sandbox
            }
        ],

        defaults: {
            isActive: false,
            isVisible: true,
            isOutside: false
        },

        computed: {
            labelClass: {
                depends: ['method'],
                get: function(fields) {
                    switch(fields.method.toUpperCase()) {
                        case 'GET':
                            return 'primary';
                        case 'POST':
                            return 'success';
                        case 'OPTIONS':
                        case 'HEAD':
                            return 'info';
                        case 'PUT':
                        case 'PATCH':
                            return 'warning';
                        case 'DELETE':
                            return 'danger';
                        default:
                            return 'default';
                    }
                }
            }
        },

        toJSON: function() {
            var json = Backbone.AssociatedModel.prototype.toJSON.apply(this, arguments);

            delete json.isVisible;
            delete json.isOutside;

            return json;
        }
    });

    entities.Methods = Backbone.Collection.extend({
        model: entities.Method
    });

    entities.ActiveMethods = entities.Methods.extend({
        initialize: function() {

            var modelEvents = [
                'change:sandbox'
            ];

            _.each(['headers', 'requirements', 'filters', 'parameters'], function(relation) {
                _.each(['add', 'remove'], function(action) {
                    modelEvents.push(action + ':sandbox.' + relation);
                });
                modelEvents.push('change:sandbox.' + relation + '[*]');
            });

            this.on('add', function(model) {
                this.listenTo(model, modelEvents.join(' '), function() {
                    this.trigger('update', model);
                });
            }, this);
        }
    });

    entities.Resource = Backbone.AssociatedModel.extend({
        defaults: {
            isVisible: true
        },
        relations: [
            {
                type: Backbone.Many,
                key: 'children',
                relatedModel: entities.Method
            }
        ]
    });
    entities.Section = Backbone.AssociatedModel.extend({
        defaults: {
            isVisible: true
        },
        relations: [
            {
                type: Backbone.Many,
                key: 'children',
                relatedModel: entities.Resource
            }
        ]
    });
    entities.Sections = Backbone.Collection.extend({
        model: function(attrs, options) {
            if(attrs.type == 'resource') {
                return new entities.Resource(attrs, options);
            } else {
                return new entities.Section(attrs, options);
            }
        }
    });
});