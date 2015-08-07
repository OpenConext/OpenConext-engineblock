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

        symlink: {
            classic: {
                files: [
                    { expand: false, overwrite: true, src: ['../www/authentication/css'], dest: '../www/profile/css' },
                    { expand: false, overwrite: true, src: ['../www/authentication/javascript'], dest: '../www/profile/javascript' },
                    { expand: false, overwrite: true, src: ['../www/authentication/media'], dest: '../www/profile/media' }
                ]
            },
            material: {
                files: [
                    { expand: false, overwrite: true, src: ['../www/authentication/stylesheets'], dest: '../www/profile/stylesheets' },
                    { expand: false, overwrite: true, src: ['../www/authentication/javascripts'], dest: '../www/profile/javascripts' },
                    { expand: false, overwrite: true, src: ['../www/authentication/images'], dest: '../www/profile/images' }
                ]
            }
        },
        uglify: {
            classic: {

            },
            material: {
                files: {
                    '../www/authentication/javascripts/application.js': [
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
            src: '../www/authentication/stylesheets/*.css'
          }
        },
        compass: {
            classic: {

            },
            material: {
                options: {
                    sassDir: 'material/stylesheets',
                    cssDir: '../www/authentication/stylesheets',
                    imagesDir: 'material/images',
                    outputStyle: 'compressed',
                    raw: 'preferred_syntax = :sass\n'
                }
            }
        },
        copy: {
            classic: {
                files: [
                    { expand: true, cwd: 'classic/templates/layouts/', src: ['**'], dest: '../application/layouts' },
                    { expand: true, cwd: 'classic/templates/modules/', src: ['**'], dest: '../application/modules' },
                    { expand: true, cwd: 'classic/media/', src: ['**'], dest: '../www/authentication/media' },
                    { expand: true, cwd: 'classic/css/', src: ['**'], dest: '../www/authentication/css' },
                    { expand: true, cwd: 'classic/javascript/', src: ['**'], dest: '../www/authentication/javascript' }
                ]
            },
            material: {
                files: [
                    { expand: true, cwd: 'material/templates/layouts/', src: ['**'], dest: '../application/layouts' },
                    { expand: true, cwd: 'material/templates/modules/', src: ['**'], dest: '../application/modules' },
                    { expand: true, cwd: 'material/images/', src: ['**'], dest: '../www/authentication/images' },
                    { expand: true, cwd: 'bower_components/html5shiv/dist', src: ['html5shiv.min.js'], dest: '../www/authentication/javascripts' }
                ]
            }
        },
        clean: {
            options: {
                force: true
            },
            classic: {
                src: [
                    '../application/layouts',
                    '../application/modules/Authentication/View',
                    '../application/modules/Default/View',
                    '../application/modules/Logout/View',
                    '../application/modules/Profile/View',
                    '../www/authentication/media/**/*',
                    '../www/authentication/css/**/*',
                    '../www/authentication/javascript/**/*'
                ]
            },
            material: {
                src: [
                    '../application/layouts',
                    '../application/modules/Authentication/View',
                    '../application/modules/Default/View',
                    '../application/modules/Logout/View',
                    '../application/modules/Profile/View',
                    '../www/authentication/images/**/*',
                    '../www/authentication/javascripts/**/*',
                    '../www/authentication/stylesheets/**/*'
                ]
            }
        },
        shell: {
            classic: {
                command: [
                    'rm ../www/profile/media',
                    'rm ../www/profile/css',
                    'rm ../www/profile/javascript'
                ].join('&&') + ' || true'
            },
            material: {
                command: [
                    'rm ../www/profile/images',
                    'rm ../www/profile/javascripts',
                    'rm ../www/profile/stylesheets'
                ].join('&&') + ' || true'
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
                        'application/layouts/**/*.phtml',
                        'application/modules/**/*.phtml',
                        'www/authentication/javascripts/**/*.js',
                        'www/authentication/stylesheets/**/*.css'
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
                  'shell:' + themeConfig.current,
                  'copy:' + theme,
                  'compass:' + theme
                ];

            if (theme !== 'classic') {
              tasks.push('postcss:' + theme);
            }

            tasks.push('uglify:' + theme);
            tasks.push('add_comment:' + theme);
            tasks.push('symlink:' + theme);
            tasks.push('string-replace:layoutconfig');


            grunt.task.run(tasks);

            themeConfig.current = theme;
            grunt.file.write('./config/theme.json', JSON.stringify(themeConfig));

            return true;
        }

        grunt.log.error('No such theme: ' + theme);
    });
};
