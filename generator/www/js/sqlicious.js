
(function($, window, document) {

	$(function() {
		
		var SQLicious = Ember.Application.create({
			rootElement: '#content',
			LOG_TRANSITIONS: true
		});
		
		SQLicious.getAPIUrl = function(url)
		{
			console.log(window.location.href.replace(window.location.hash,""));
			
			return window.location.href.replace(window.location.hash,"").replace("index.php","") + url.substring(1);
		}
		
		SQLicious.ajax = function(options)
		{
			options.dataType = 'json';
			
			$.ajax(SQLicious.getAPIUrl(options.url),{
				success : options.success,
				data : options.data,
				dataType: options.dataType
			});
		}
		
		// app controller
		SQLicious.ApplicationController = Ember.Controller.extend();
		SQLicious.ApplicationView = Ember.View.extend({
			templateName: 'sqlicious-app-template'
		});
		
		SQLicious.Model =  Ember.Object.extend();
		
		SQLicious.Database = SQLicious.Model.extend({
			databaseName: null
		});
		SQLicious.DatabaseTable = SQLicious.Model.extend({
			tableName: null,
			database: new SQLicious.Database()
		});
		
		SQLicious.Database.reopenClass({
			
			findAll: function()
			{
				var dbs = new Array()
				
				$.each(config.db,function(index,db)
				{
					dbs.push(SQLicious.Database.create({
						'databaseName': db.databaseName
					}));
				});
					
				return dbs;
			},
			
			find: function(databaseName)
			{
				var database;
				
				$.each(config.db,function(index,db)
				{
					if(db.databaseName == databaseName)
					{
						database = SQLicious.Database.create({
							'databaseName': db.databaseName
						});
					}
				});
				
				return database;
			}
		});
		
		SQLicious.Router.map(function() {
			
			this.route('generate', {
				path: '/generate'
			});
			
			this.route('database', {
				path: '/database/:databaseName'
			});
			
			this.route('databaseGenerate', {
				path: '/database/:databaseName/generate'
			});
			
			this.route('table', {
				path: '/database/:databaseName/table/:tableName'
			});
			
			this.route('objectCreation', {
				path: '/database/:databaseName/table/:tableName/objectCreation'
			});
			
			this.route('structure', {
				path: '/database/:databaseName/table/:tableName/structure'
			});
			
			this.route('extendedStub', {
				path: '/database/:databaseName/table/:tableName/extendedStub'
			});
			
			this.route('api', {
				path: '/database/:databaseName/table/:tableName/api'
			});
			
		});
		
		// dashboard (index)
		SQLicious.IndexController = Ember.Controller.extend();
		SQLicious.IndexView = Ember.View.extend();
		SQLicious.IndexRoute = Ember.Route.extend({
			setupController: function(controller) {
				controller.set('dbs',SQLicious.Database.findAll());
			}
		});
		
		// database page is a list of tables
		SQLicious.DatabaseView = Ember.View.extend({
			
			keyUp: function(event){
				
				var search = this.$('input').val();

				this.$('#list-of-tables li').each(function(index,item)
				{
					var li = $(item);
					
					search == "" || li.text().toUpperCase().indexOf(search.toUpperCase()) >= 0 ? li.show() : li.hide();

				});
			}
		});
		
		SQLicious.DatabaseController = Ember.ObjectController.extend({});
		SQLicious.DatabaseRoute = Ember.Route.extend({
			
			setupController: function()
			{
				this.databaseTablesController = this.controllerFor('databaseTables');
				this.databaseTablesController.set('content',[]);
			},
			
			activate: function()
			{
				SQLicious.ajax({ 
					url : '/api/tables/list.php',
					data : {
						'database' : this.context.databaseName
						},
					success : function(resp)
					{
						this.databaseTablesController.set('database',SQLicious.Database.find(resp.databaseName));
						this.databaseTablesController.set('content',resp.tables);
					}.bind(this)
				});
			},
			
			model: function(params)
			{
				return SQLicious.Database.find(params.databaseName);
			},
			
			serialize: function(model,params)
			{
				if(model)
				{
					return { databaseName: model.databaseName };
				}
				else
				{
					return {};
				}
				
			}
		});
		
		// sub-template controller and view for database page
		SQLicious.DatabaseTablesView = Ember.View.extend();
		SQLicious.DatabaseTablesController = Ember.ArrayController.extend();
		
		SQLicious.GenerateView = Ember.View.extend({
			
			didInsertElement: function()
			{
				this.$('.modal').modal();
			}
			
		});
		SQLicious.GenerateRoute = Ember.Route.extend({
			
			activate: function()
			{
				SQLicious.ajax({ 
					url : '/api/generator/generate.php',
					success : function(resp)
					{
						if(resp.errors != null && resp.errors.length > 0)
						{
							alert(resp.errors);
						}
						
						window.location.hash = '#';
						window.location.reload(false);
						
					}.bind(this)
				});
			}
		});
	
		SQLicious.DatabaseGenerateView = Ember.View.extend({
			
			didInsertElement: function()
			{
				this.$('.modal').modal();
			}
		});
		SQLicious.DatabaseGenerateRoute = Ember.Route.extend({
			
			activate: function()
			{
				SQLicious.ajax({ 
					url : '/api/generator/generate.php',
					data : { 'database' : this.context.databaseName },
					success : function(resp)
					{
						if(resp.errors != null && resp.errors.length > 0)
						{
							alert(resp.errors);
							window.location.hash = '#';
							window.location.reload(false);
						}
						else
						{
							window.location.hash = '#/database/' + resp.databaseName + '/';
							window.location.reload(false);
						}
					}.bind(this)
				});
			},
			
			model: function(params)
			{
				return SQLicious.Database.find(params.databaseName);
			},
			
			serialize: function(model,params)
			{
				if(model)
				{
					return { databaseName: model.databaseName };
				}
				else
				{
					return {};
				}
			}
			
		});
		
		SQLicious.TableView = Ember.View.extend();
		SQLicious.TableController = Ember.ObjectController.extend({});
		SQLicious.TableRoute = Ember.Route.extend({
			
			setupController: function(controller) {
				controller.set('database',SQLicious.Database.find(this.context.databaseName));
			},
			
			model: function(params)
			{
				return SQLicious.DatabaseTable.create(
				{
					tableName : params.tableName,
					database : SQLicious.Database.find(params.databaseName),
					databaseName: params.databaseName
				});
				
			},
			
			serialize: function(model,params)
			{
				if(model)
				{
					return { databaseName: model.databaseName, tableName : model.tableName };
				}
				else
				{
					return {};
				}
			}
			
		});
		
		SQLicious.ObjectCreationView = Ember.View.extend();
		SQLicious.ObjectCreationController = Ember.ObjectController.extend({});
		SQLicious.ObjectCreationRoute = Ember.Route.extend({
			
			templateName: 'objectCreation',
			
			setupController: function(controller) {
				controller.set('database',SQLicious.Database.find(this.context.databaseName));
				controller.set('table',this.model({ 'databaseName':this.context.databaseName,'tableName':this.context.tableName }));
			},
			
			model: function(params)
			{
				return SQLicious.DatabaseTable.create(
				{
					tableName : params.tableName,
					database : SQLicious.Database.find(params.databaseName),
					databaseName: params.databaseName
				});
				
			},
			
			serialize: function(model,params)
			{
				return { databaseName: model.databaseName, tableName : model.tableName };
			},
			
			activate: function()
			{
				SQLicious.ajax({ 
					url : '/api/table/object_creation.php',
					data : {
						'database' : this.context.databaseName, 
						'table' : this.context.tableName
						},
					success : function(resp)
					{
						this.controller.set('responseTemplate',resp.code);
					}.bind(this)
				});
			}
			
		});
		
		SQLicious.ExtendedStubView = Ember.View.extend();
		SQLicious.ExtendedStubController = Ember.ObjectController.extend({});
		SQLicious.ExtendedStubRoute = Ember.Route.extend({
			
			templateName: 'extendedStub',
			
			setupController: function(controller) {
				controller.set('database',SQLicious.Database.find(this.context.databaseName));
				controller.set('table',this.model({
					'databaseName':this.context.databaseName,
					'tableName':this.context.tableName
				}));
			},
			
			model: function(params)
			{
				return SQLicious.DatabaseTable.create(
				{
					tableName : params.tableName,
					database : SQLicious.Database.find(params.databaseName),
					databaseName: params.databaseName
				});
			},
			
			serialize: function(model,params)
			{
				return { databaseName: model.databaseName, tableName : model.tableName };
			},
			
			activate: function()
			{
				SQLicious.ajax({ 
					url : '/api/table/extended_stub.php',
					data : {
						'database' : this.context.databaseName, 
						'table' : this.context.tableName
						},
					success : function(resp)
					{
						this.controller.set('responseTemplate',resp.code);
					}.bind(this)
				});
			}
			
		});
		
		
		SQLicious.StructureView = Ember.View.extend();
		SQLicious.StructureController = Ember.ObjectController.extend({});
		SQLicious.StructureRoute = Ember.Route.extend({
			
			templateName: 'structure',
			
			setupController: function(controller) {
				controller.set('database',SQLicious.Database.find(this.context.databaseName));
				controller.set('table',this.model({
					'databaseName':this.context.databaseName,
					'tableName':this.context.tableName
					}));
			},
			
			model: function(params)
			{
				return SQLicious.DatabaseTable.create(
				{
					tableName : params.tableName,
					database : SQLicious.Database.find(params.databaseName),
					databaseName: params.databaseName
				});
				
			},
			
			serialize: function(model,params)
			{
				return { databaseName: model.databaseName, tableName : model.tableName };
			},
			
			activate: function()
			{
				SQLicious.ajax({ 
					url : '/api/table/table_structure.php',
					data : {
						'database' : this.context.databaseName, 
						'table' : this.context.tableName
						},
					success : function(resp)
					{
						this.controller.set('responseTemplate',resp.html);
					}.bind(this)
				});
			}
			
		});
		
	});

}(window.jQuery, window, document));

// drop a {{debug}} in your template and get a nice output to your console
//Handlebars.registerHelper("debug", function(optionalValue) {console.log("Current Context");console.log("====================");console.log(this);if (optionalValue) {console.log("Value");console.log("====================");console.log(optionalValue);}});
