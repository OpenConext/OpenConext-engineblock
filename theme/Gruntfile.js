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

        uglify: {
            classic: {

            },
            material: {

            },
            dist: {
                files: {
                    '../www/authentication/javascripts/application.js': [
                        '<%= config.theme %>/javascripts/application.js'
                    ]
                }
            }
        },
        compass: {
            classic: {

            },
            material: {

            },
            dist: {
                options: {
                    sassDir: '<%= config.theme %>/stylesheets',
                    cssDir: '../www/authentication/stylesheets',
                    imagesDir: '<%= config.theme %>/images',
                    raw: 'preferred_syntax = :sass\n'
                }
            }
        },
        copy: {
            classic: {
                files: [
                    { expand: true, cwd: 'classic/templates/layouts/', src: ['**'], dest: '../application/layouts' },
                    { expand: true, cwd: 'classic/templates/modules/', src: ['**'], dest: '../application/modules' },
                    { expand: true, cwd: 'classic/media/', src: ['**'], dest: '../www/authentication/media' }
                ]
            },
            material: {
                files: [
                    { expand: true, cwd: 'material/templates/layouts/', src: ['**'], dest: '../application/layouts' },
                    { expand: true, cwd: 'material/templates/modules/', src: ['**'], dest: '../application/modules' },
                    { expand: true, cwd: 'material/images/', src: ['**'], dest: '../www/authentication/images' }
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
                    '../www/authentication/media',
                    '../www/authentication/css',
                    '../www/authentication/javascript',
                    '../www/profile/media',
                    '../www/profile/css',
                    '../www/profile/javascript'
                ]
            },
            material: {
                src: [
                    '../application/layouts',
                    '../application/modules/Authentication/View',
                    '../application/modules/Default/View',
                    '../application/modules/Logout/View',
                    '../application/modules/Profile/View',
                    '../www/authentication/images',
                    '../www/authentication/javascripts',
                    '../www/authentication/stylesheets',
                    '../www/profile/images',
                    '../www/profile/javascripts',
                    '../www/profile/stylesheets'
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
            var themeConfig = JSON.parse(grunt.file.read('./config/theme.json'));

            grunt.task.run(
                [
                    'clean:' + themeConfig.current,
                    'copy:' + theme,
                    'string-replace:layoutconfig'
                ]
            );

            themeConfig.current = theme;
            grunt.file.write('./config/theme.json', JSON.stringify(themeConfig));

            return true;
        }

        grunt.log.error('No such theme: ' + theme);
    });
};