module.exports = function(grunt) {
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    copy: {
      main: {
        files: [
          {
            cwd: 'node_modules/jquery/dist',
            expand: true,
            src: [
              'jquery.min.js',
              'jquery.min.map'
            ],
            dest: 'public'
          },
          {
            cwd: 'node_modules/google-u2f-api.js',
            expand: true,
            src: [
              'u2f-api.js'
            ],
            dest: 'public'
          }
        ]
      }
    },
    sass: {
      dist: {
        files: {
          'public/style.min.css': 'sass/style.scss',
        }
      }
    },
    watch: {
      sass: {
        files: [
          'sass/style.scss',
          'sass/elements/*.scss',
          'sass/mixins/*.scss',
          'sass/settings/*.scss',
        ],
        tasks: ['sass']
      }
    }
  });

  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-sass');
  grunt.loadNpmTasks('grunt-contrib-watch');

  grunt.registerTask('default', ['sass', 'watch']);
}
