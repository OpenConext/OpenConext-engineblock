module.exports = function(grunt) {

    var config = {
        themes: ['classic', 'material'],
        theme: 'material',
        outputDir: '../'
    };

    require('load-grunt-tasks')(grunt);

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        config: config,

        //symlink: {
        //    classic: {
        //        files: [
        //            { expand: false, overwrite: true, src: ['../www/authentication/css'], dest: '../www/profile/css' },
        //            { expand: false, overwrite: true, src: ['../www/authentication/javascript'], dest: '../www/profile/javascript' },
        //            { expand: false, overwrite: true, src: ['../www/authentication/media'], dest: '../www/profile/media' }
        //        ]
        //    },
        //    material: {
        //        files: [
        //            { expand: false, overwrite: true, src: ['../www/authentication/stylesheets'], dest: '../www/profile/stylesheets' },
        //            { expand: false, overwrite: true, src: ['../www/authentication/javascripts'], dest: '../www/profile/javascripts' },
        //            { expand: false, overwrite: true, src: ['../www/authentication/images'], dest: '../www/profile/images' }
        //        ]
        //    }
        //},
        uglify: {
            classic: {

            },
            material: {
                files: {
                    '../web/javascripts/application.js': [
                        'bower_components/jquery/dist/jquery.min.js',
                        'bower_components/jquery.lazyload/jquery.lazyload.js',
                        'bower_components/js-cookie/src/js.cookie.js',
                        'bower_components/easyModal.js/jquery.easyModal.js',
                        'bower_components/fastclick/lib/fastclick.js',
                        'bower_components/underscore/underscore.js',
                        '<%= config.theme %>/javascripts/application.js'
                    ]
                }
            }
        },
        postcss: {
          options: {
            map: true,
            processors: [
              require('autoprefixer-core')({browsers: 'last 3 versions'}),
              require('csswring')
            ]
          },
          material: {
            src: '../web/stylesheets/*.css'
          }
        },
        compass: {
            classic: {

            },
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
            classic: {
                files: [
                    { expand: true, cwd: 'classic/templates/layouts/', src: ['**'], dest: '../app/Resources/views/layouts' },
                    { expand: true, cwd: 'classic/templates/modules/', src: ['**'], dest: '../app/Resources/views/modules' },
                    { expand: true, cwd: 'classic/media/', src: ['**'], dest: '../web/media' },
                    { expand: true, cwd: 'classic/css/', src: ['**'], dest: '../web/css' },
                    { expand: true, cwd: 'classic/javascript/', src: ['**'], dest: '../web/javascript' }
                ]
            },
            material: {
                files: [
                    { expand: true, cwd: 'material/templates/layouts/', src: ['**'], dest: '../app/Resources/views/layouts' },
                    { expand: true, cwd: 'material/templates/modules/', src: ['**'], dest: '../app/Resources/views/modules' },
                    { expand: true, cwd: 'material/images/', src: ['**'], dest: '../web/images' },
                    { expand: true, cwd: 'bower_components/html5shiv/dist', src: ['html5shiv.min.js'], dest: '../web/javascripts' }
                ]
            }
        },
        clean: {
            options: {
                force: true
            },
            classic: {
                src: [
                    '../app/Resources/views/layouts',
                    '../app/Resources/views/modules/Authentication/View',
                    '../app/Resources/views/modules/Default/View',
                    '../app/Resources/views/modules/Logout/View',
                    '../app/Resources/views/modules/Profile/View',
                    '../web/media/**/*',
                    '../web/css/**/*',
                    '../web/javascript/**/*'
                ]
            },
            material: {
                src: [
                    '../app/Resources/views/layouts',
                    '../app/Resources/views/modules/Authentication/View',
                    '../app/Resources/views/modules/Default/View',
                    '../app/Resources/views/modules/Logout/View',
                    '../app/Resources/views/modules/Profile/View',
                    '../web/images/**/*',
                    '../web/javascripts/**/*',
                    '../web/stylesheets/**/*'
                ]
            }
        },
        //shell: {
        //    classic: {
        //        command: [
        //            'rm ../www/profile/media',
        //            'rm ../www/profile/css',
        //            'rm ../www/profile/javascript'
        //        ].join('&&') + ' || true'
        //    },
        //    material: {
        //        command: [
        //            'rm ../www/profile/images',
        //            'rm ../www/profile/javascripts',
        //            'rm ../www/profile/stylesheets'
        //        ].join('&&') + ' || true'
        //    }
        //},
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
            classic: {
                files: 'classic/**',
                tasks: ['theme:classic']
            },
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
                  //'shell:' + themeConfig.current,
                  'copy:' + theme,
                  'compass:' + theme
                ];

            if (theme !== 'classic') {
              tasks.push('postcss:' + theme);
            }

            tasks.push('uglify:' + theme);
            tasks.push('add_comment:' + theme);
            //tasks.push('symlink:' + theme);
            tasks.push('string-replace:layoutconfig');


            grunt.task.run(tasks);

            themeConfig.current = theme;
            grunt.file.write('./config/theme.json', JSON.stringify(themeConfig));

            return true;
        }

        grunt.log.error('No such theme: ' + theme);
    });
};
