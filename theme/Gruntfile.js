module.exports = function(grunt) {

    var config = {
        themes: ['material'],
        theme: 'material',
        outputDir: '../'
    };

    require('load-grunt-tasks')(grunt);

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        config: config,
        browserify: {
            dist: {
                options: {
                    transform: [
                        [
                            "babelify", {
                                presets: [
                                    ['es2015', {'loose': true}]
                                ],
                                plugins: [
                                    'transform-es3-property-literals',
                                    'transform-es3-member-expression-literals'
                                ]
                            },
                        ],
                    ]
                },
                files: {
                    "../web/javascripts/application.js": ["<%= config.theme %>/javascripts/application.js"]
                }
            }
        },
        uglify: {
            dist: {
                files: {
                    '../web/javascripts/application.min.js': [
                        'node_modules/es5-shim/es5-shim.min.js',
                        'node_modules/es6-shim/es6-shim.min.js',
                        'node_modules/html5shiv/dist/html5-shiv.min.js',
                        'node_modules/dom-polyfills/polyfills.js',
                        '../web/javascripts/application.js'
                    ]
                },
            },
        },
        postcss: {
          options: {
            map: true,
            processors: [
              require('autoprefixer-core')({browsers: [
                  'ie >= 8',
                  'chrome >= 20',
                  'firefox >= 20',
                  'opera >= 11',
                  'safari >= 5',
                  'android >= 4',
                  'ios >= 4',
                  'last 3 versions'
              ]}),
              require('csswring')
            ]
          },
          material: {
            src: '../web/stylesheets/*.css'
          }
        },
        compass: {
            material: {
                options: {
                    sassDir: 'material/stylesheets',
                    cssDir: '../web/stylesheets',
                    imagesDir: 'material/images',
                    outputStyle: 'compressed',
                    raw: 'preferred_syntax = :sass\n'
                }
            }
        },
        copy: {
            material: {
                files: [
                    { expand: true, cwd: 'material/templates/layouts/', src: ['**'], dest: '../app/Resources/views/layouts' },
                    { expand: true, cwd: 'material/templates/modules/', src: ['**'], dest: '../app/Resources/views/modules' },
                    { expand: true, cwd: 'material/images/', src: ['**'], dest: '../web/images' },
                ]
            }
        },
        clean: {
            options: {
                force: true
            },
            material: {
                src: [
                    '../app/Resources/views/layouts',
                    '../app/Resources/views/modules/Authentication/View',
                    '../app/Resources/views/modules/Default/View',
                    '../app/Resources/views/modules/Logout/View',
                    '../web/images/**/*',
                    '../web/javascripts/**/*',
                    '../web/stylesheets/**/*'
                ]
            },
            nonMinifiedJavaScript: {
                src: [
                    '../web/javascripts/application.js'
                ]
            }
        },
        'string-replace': {
            layoutconfig: {
                files: {
                    '../application/configs/application.ini': '../application/configs/application.ini'
                },
                options: {
                    replacements: [
                        {
                            pattern: 'defaults.layout     = "1-column-blue-grey"',
                            replacement: 'defaults.layout     = "default"'
                        }
                    ]
                }
            }
        },
        watch: {
            material: {
                files: 'material/**',
                tasks: ['theme:material']
            }
        },
        add_comment: {
            material: {
                options: {
                    comments: ["This file is generated. Please edit the files of the appropriate theme in the 'theme/' directory."],
                    syntaxes: {
                        '.phtml': ['<?php /*', '*/ ?>'],
                        '.css': ['/*', '*/']
                    }
                },
                files: [{
                    expand: true,
                    cwd: __dirname + '/..',
                    src: [
                        'app/Resources/views/layouts/**/*.phtml',
                        'app/Resources/views/modules/**/*.phtml',
                        'web/javascripts/**/*.js',
                        'wev/stylesheets/**/*.css'
                    ],
                    dest: __dirname + '/..'
                }]
            }
        }
    });

    // Default task(s).
    grunt.registerTask('default', ['theme:material']);

    grunt.registerTask('theme', 'Apply a theme to EngineBlock', function(theme){
        if (arguments.length === 0) {
            grunt.log.error('A theme name is expected; grunt theme:material');
        }

        if (config.themes.indexOf(theme) > -1) {
            // start tasks
            config.theme = theme;
            var themeConfig = JSON.parse(grunt.file.read('./config/theme.json')),
                tasks = [
                  'clean:' + themeConfig.current,
                  'copy:' + theme,
                  'compass:' + theme
                ];

            tasks.push('postcss:' + theme);
            tasks.push('browserify');
            tasks.push('uglify');
            tasks.push('clean:nonMinifiedJavaScript');
            tasks.push('add_comment:' + theme);
            tasks.push('string-replace:layoutconfig');

            grunt.task.run(tasks);

            themeConfig.current = theme;
            grunt.file.write('./config/theme.json', JSON.stringify(themeConfig));

            return true;
        }

        grunt.log.error('No such theme: ' + theme);
    });
};
