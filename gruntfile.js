'use strict';
module.exports = function(grunt) {

    // load all grunt tasks matching the `grunt-*` pattern
    // Ref. https://npmjs.org/package/load-grunt-tasks
    require('load-grunt-tasks')(grunt);
    grunt.initConfig({

        // Check text domain
        checktextdomain: {
            options: {
                text_domain: ['buddypress-share', 'wbcom-shared'], // Specify allowed domain(s)
                keywords: [ // List keyword specifications
                    '__:1,2d',
                    '_e:1,2d',
                    '_x:1,2c,3d',
                    'esc_html__:1,2d',
                    'esc_html_e:1,2d',
                    'esc_html_x:1,2c,3d',
                    'esc_attr__:1,2d',
                    'esc_attr_e:1,2d',
                    'esc_attr_x:1,2c,3d',
                    '_ex:1,2c,3d',
                    '_n:1,2,4d',
                    '_nx:1,2,4c,5d',
                    '_n_noop:1,2,3d',
                    '_nx_noop:1,2,3c,4d'
                ]
            },
            target: {
                files: [{
                    src: [
                        '*.php',
                        '**/*.php',
                        '!node_modules/**',
                        '!options/framework/**',
                        '!tests/**',
                        '!plugin-update-checker/**',
                    ], // all php
                    expand: true
                }]
            }
        },
        // make po files
        makepot: {
            target: {
                options: {
                    cwd: '.', // Directory of files to internationalize.
                    domainPath: 'languages/', // Where to save the POT file.
                    exclude: ['node_modules/*', 'options/framework/*', 'plugin-update-checker/*'], // List of files or directories to ignore.
                    mainFile: 'index.php', // Main project file.
                    potFilename: 'buddypress-share.pot', // Name of the POT file.
                    potHeaders: { // Headers to add to the generated POT file.
                        poedit: true, // Includes common Poedit headers.
                        'Last-Translator': 'Varun Dubey',
                        'Language-Team': 'Wbcom Designs',
                        'report-msgid-bugs-to': '',
                        'x-poedit-keywordslist': true // Include a list of all possible gettext functions.
                    },
                    type: 'wp-plugin', // Type of project (wp-plugin or wp-theme).
                    updateTimestamp: true // Whether the POT-Creation-Date should be updated without other changes.
                }
            }
        },
        // Task for CSS minification
        cssmin: {
            public: {
                files: [{
                    expand: true,
                    cwd: 'public/css/', // Source directory for frontend CSS files
                    src: ['*.css', '!*.min.css', '!vendor/*.css'], // Minify all frontend CSS files except already minified ones
                    dest: 'public/css/', // Destination directory for minified frontend CSS
                    ext: '.min.css', // Extension for minified files
                },
                {
                    expand: true,
                    cwd: 'public/css-rtl/', // Source directory for RTL CSS files
                    src: ['*.css', '!*.min.css', '!vendor/*.css'], // Minify all .css files except already minified ones
                    dest: 'public/css-rtl/', // Destination directory for minified CSS
                    ext: '.min.css' // Output file extension
                }],
            },
            admin: {
                files: [{
                    expand: true,
                    cwd: 'admin/css/', // Source directory for admin CSS files
                    src: ['*.css', '!*.min.css', '!vendor/*.css'], // Minify all admin CSS files except already minified ones
                    dest: 'admin/css/', // Destination directory for minified admin CSS
                    ext: '.min.css', // Extension for minified files
                },
                {
                    expand: true,
                    cwd: 'admin/css-rtl/', // Source directory for RTL CSS files
                    src: ['*.css', '!*.min.css', '!vendor/*.css'], // Minify all .css files except already minified ones
                    dest: 'admin/css-rtl/', // Destination directory for minified CSS
                    ext: '.min.css' // Output file extension
                }],
            },
            wbcom: {
                files: [{
                    expand: true,
                    cwd: 'admin/wbcom/assets/css/', // Source directory for admin CSS files
                    src: ['*.css', '!*.min.css', '!vendor/*.css'], // Minify all admin CSS files except already minified ones
                    dest: 'admin/wbcom/assets/css/', // Destination directory for minified admin CSS
                    ext: '.min.css', // Extension for minified files
                },
                {
                    expand: true,
                    cwd: 'admin/wbcom/assets/css-rtl/', // Source directory for RTL CSS files
                    src: ['*.css', '!*.min.css', '!vendor/*.css'], // Minify all .css files except already minified ones
                    dest: 'admin/wbcom/assets/css-rtl/', // Destination directory for minified CSS
                    ext: '.min.css' // Output file extension
                }],
            },
        },
        // rtlcss
        rtlcss: {
            myTask: {
                options: {
                    // Generate source maps
                    map: { inline: false },
                    // RTL CSS options
                    opts: {
                        clean: false
                    },
                    // RTL CSS plugins
                    plugins: [],
                    // Save unmodified files
                    saveUnmodified: true,
                },
                files: [
                    {
                        expand: true,
                        cwd: 'public/css', // Source directory for public CSS
                        src: ['**/*.css', '!**/*.min.css', '!vendor/**/*.css'], // Source files, excluding vendor CSS
                        dest: 'public/css-rtl', // Destination directory for public RTL CSS
                        flatten: true // Prevents creating subdirectories
                    },
                    {
                        expand: true,
                        cwd: 'admin/css', // Source directory for public CSS
                        src: ['**/*.css', '!**/*.min.css', '!vendor/**/*.css'], // Source files, excluding vendor CSS
                        dest: 'admin/css-rtl', // Destination directory for public RTL CSS
                        flatten: true // Prevents creating subdirectories
                    },
                    {
                        expand: true,
                        cwd: 'admin/wbcom/assets/css', // Source directory for public CSS
                        src: ['**/*.css', '!**/*.min.css', '!vendor/**/*.css'], // Source files, excluding vendor CSS
                        dest: 'admin/wbcom/assets/css-rtl', // Destination directory for public RTL CSS
                        flatten: true // Prevents creating subdirectories
                    },
                ]
            }
        },
        shell: {
            makepot_js: {
                command: 'wp i18n make-pot . languages/buddypress-share.pot',
            }
        },
        // JS minification (uglify)
        uglify: {
            public: {
                options: {
                    mangle: false, // Prevents variable name mangling
                },
                files: [{
                    expand: true,
                    cwd: 'public/js/', // Source directory for frontend JS files
                    src: ['*.js', '!*.min.js', '!vendor/*.js'], // Minify all frontend JS files except already minified ones
                    dest: 'public/js/', // Destination directory for minified frontend JS
                    ext: '.min.js', // Extension for minified files
                }],
            },
            admin: {
                options: {
                    mangle: false, // Prevents variable name mangling
                },
                files: [{
                    expand: true,
                    cwd: 'admin/js/', // Source directory for admin JS files
                    src: ['*.js', '!*.min.js', '!vendor/*.js'], // Minify all admin JS files except already minified ones
                    dest: 'admin/js/', // Destination directory for minified admin JS
                    ext: '.min.js', // Extension for minified files
                }],
            },
            wbcom: {
                options: {
                    mangle: false, // Prevents variable name mangling
                },
                files: [{
                    expand: true,
                    cwd: 'admin/wbcom/assets/js', // Source directory for admin JS files
                    src: ['*.js', '!*.min.js', '!vendor/*.js'], // Minify all admin JS files except already minified ones
                    dest: 'admin/wbcom/assets/js', // Destination directory for minified admin JS
                    ext: '.min.js', // Extension for minified files
                }],
            },
        },
        // Task for watching file changes
        watch: {
            css: {
                files: ['public/css/*.css', '!public/css/*.min.css'], // Watch for changes in frontend CSS files
                tasks: ['cssmin:public'], // Run frontend CSS minification task
            },
            js: {
                files: ['public/js/*.js', '!public/js/*.min.js'], // Watch for changes in frontend JS files
                tasks: ['uglify:public'], // Run frontend JS minification task
            },
            php: {
                files: ['**/*.php'], // Watch for changes in PHP files
                tasks: ['checktextdomain'], // Run text domain check
            },
        },
        // Clean dist directory
        clean: {
            dist: {
                src: ['dist']
            },
            temp: {
                src: ['dist/buddypress-activity-share-pro']
            }
        },
        // Copy files to dist
        copy: {
            dist: {
                files: [{
                    expand: true,
                    src: [
                        '**',
                        '!node_modules/**',
                        '!dist/**',
                        '!docs/**',
                        '!.git/**',
                        '!.gitignore',
                        '!.gitattributes',
                        '!gruntfile.js',
                        '!Gruntfile.js',
                        '!package.json',
                        '!package-lock.json',
                        '!composer.json',
                        '!composer.lock',
                        '!phpcs.xml',
                        '!CLAUDE.md',
                        '!**/*.map',
                        '!**/.DS_Store',
                        '!admin/css/*.css',
                        '!admin/css-rtl/*.css',
                        '!public/css/*.css',
                        '!public/css-rtl/*.css',
                        '!admin/wbcom/assets/css/*.css',
                        '!admin/wbcom/assets/css-rtl/*.css',
                        '!admin/js/*.js',
                        '!public/js/*.js',
                        '!admin/wbcom/assets/js/*.js',
                        'admin/css/*.min.css',
                        'admin/css-rtl/*.min.css',
                        'public/css/*.min.css',
                        'public/css-rtl/*.min.css',
                        'admin/wbcom/assets/css/*.min.css',
                        'admin/wbcom/assets/css-rtl/*.min.css',
                        'admin/js/*.min.js',
                        'public/js/*.min.js',
                        'admin/wbcom/assets/js/*.min.js'
                    ],
                    dest: 'dist/buddypress-activity-share-pro/'
                }]
            }
        },
        // Compress dist to ZIP
        compress: {
            dist: {
                options: {
                    archive: 'dist/buddypress-activity-share-pro-<%= grunt.file.readJSON("package.json").version %>.zip',
                    mode: 'zip'
                },
                files: [{
                    expand: true,
                    cwd: 'dist/',
                    src: ['buddypress-activity-share-pro/**'],
                    dest: '/'
                }]
            }
        }
    });

    // Load the plugins
    grunt.loadNpmTasks('grunt-wp-i18n');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-checktextdomain');
    grunt.loadNpmTasks('grunt-rtlcss');
    grunt.loadNpmTasks('grunt-shell');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-compress');

    // Register default tasks
    grunt.registerTask('default', ['cssmin', 'uglify', 'checktextdomain', 'rtlcss', 'shell:makepot_js', 'watch']);

    // Build task - minify and prepare files
    grunt.registerTask('build', [
        'checktextdomain',
        'cssmin',
        'uglify',
        'rtlcss',
        'makepot',
        'shell:makepot_js'
    ]);

    // Distribution task - create release ZIP
    grunt.registerTask('dist', [
        'build',
        'clean:dist',
        'copy:dist',
        'compress:dist',
        'clean:temp'
    ]);
};