// Gruntfile.js
module.exports = function(grunt) {
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-uglify');

    // Création du répertoire d'image pour l'application s'il n'existe pas.
    //grunt.file.mkdir('app/Resources/public/images/');

    // Configuration du projet
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        // Définition de la tache 'less'
        // https://github.com/gruntjs/grunt-contrib-less
        less: {
            development: {
                files: {
                    "web/assets/AppBundle/css/recolnat.css": "src/AppBundle/Resources/less/*.less",
                }
            },
            bootstrap: {
                files: {
                    "web/assets/vendor/css/bootstrap.css": "src/AppBundle/Resources/bootstrap-3.3.6/less/bootstrap.less"
                }
            }
        },

        // Définition de la tache 'symlink'
        // https://github.com/gruntjs/grunt-contrib-symlink
        copy: {
            css: {
                files: [{
                    expand: true,
                    cwd: 'src/AppBundle/Resources/public/css',
                    dest: 'web/assets/AppBundle/css',
                    src: ['**']
                }]
            },
            images: {
                files: [{
                    expand: true,
                    cwd: 'src/AppBundle/Resources/public/images',
                    dest: 'web/assets/AppBundle/images',
                    src: ['**']
                }]
            },
            js: {
                files: [{
                    expand: true,
                    cwd: 'src/AppBundle/Resources/public/js',
                    dest: 'web/assets/AppBundle/js',
                    src: ['**', '!default.js', '!jquery.toaster.js','!string.js']
                }]
            },
            fonts: {
                files: [{
                    expand: true,
                    cwd: 'src/AppBundle/Resources/bootstrap-3.3.6/fonts',
                    dest: 'web/assets/vendor/fonts',
                    src: ['**']
                }]
            },
            favicon: {
                files: [{
                    expand: true,
                    cwd: 'src/AppBundle/Resources/public/images',
                    dest: 'web/',
                    src: ['favicon.png'],
                    ext: '.ico'
                }]
            }
        },
        concat: {
            bootstrap: {
                src: [
                    //'web/vendor/jquery/jquery.js',
                    'src/AppBundle/Resources/bootstrap-3.3.6/js/transition.js',
                    'src/AppBundle/Resources/bootstrap-3.3.6/js/alert.js',
                    'src/AppBundle/Resources/bootstrap-3.3.6/js/modal.js',
                    //'src/AppBundle/Resources/bootstrap-3.3.6/js/bootstrap-dropdown.js',
                    //'src/AppBundle/Resources/bootstrap-3.3.6/js/bootstrap-scrollspy.js',
                    'src/AppBundle/Resources/bootstrap-3.3.6/js/tab.js',
                    'src/AppBundle/Resources/bootstrap-3.3.6/js/tooltip.js',
                    //'src/AppBundle/Resources/bootstrap-3.3.6/js/bootstrap-popover.js',
                    'src/AppBundle/Resources/bootstrap-3.3.6/js/button.js',
                    'src/AppBundle/Resources/bootstrap-3.3.6/js/collapse.js',
                    //'src/AppBundle/Resources/bootstrap-3.3.6/js/bootstrap-carousel.js',
                    //'src/AppBundle/Resources/bootstrap-3.3.6/js/bootstrap-typeahead.js',
                    //'src/AppBundle/Resources/bootstrap-3.3.6/js/bootstrap-affix.js',
                    //'web/bundles/app/js/wozbe.js'
                ],
                dest: 'web/assets/vendor/bootstrap/js/bootstrap.js'
            },
            app: {
                src: [
                    'src/AppBundle/Resources/public/js/default.js',
                    'src/AppBundle/Resources/public/js/jquery.toaster.js',
                    'src/AppBundle/Resources/public/js/string.js',
                ],
                dest: 'web/assets/AppBundle/js/recolnat.js'
            },
            jquery: {
                src: [
                    'src/AppBundle/Resources/public/jquery/jquery-1.12.3.min.js'
                ],
                dest: 'web/assets/vendor/jquery/jquery.min.js'
            }
        },
        // Lorsque l'on modifie des fichiers LESS, il faut relancer la tache 'css'
        // Lorsque l'on modifie des fichiers JS, il faut relancer la tache 'javascript'
        watch: {
            css: {
                files: ['src/AppBundle/Resources/less/*.less'],
                tasks: ['less:development']
            },
            public: {
                files: ['src/AppBundle/Resources/public/*/*'],
                tasks: ['copy', 'javascript']
            },
            /*javascript: {
                files: ['web/bundles/app/js/*.js'],
                tasks: ['javascript']
            }*/
        },
        uglify: {
            dist: {
                files: {
                    'web/assets/vendor/bootstrap/js/bootstrap.min.js': ['web/assets/vendor/bootstrap/js/bootstrap.js'],
                    'web/assets/AppBundle/js/recolnat.min.js': ['web/assets/AppBundle/js/recolnat.js']
                }
            }
        }
    });

    // Default task(s).
    grunt.registerTask('default', ['css', 'javascript', 'copy']);
    grunt.registerTask('css', ['less']);
    grunt.registerTask('javascript', ['concat', 'uglify']);
    grunt.registerTask('assets:install', ['copy']);
    grunt.registerTask('deploy', ['assets:install', 'default']);
};
