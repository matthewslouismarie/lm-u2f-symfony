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
            dest: 'assets'
          },
          {
            cwd: 'node_modules/google-u2f-api.js',
            expand: true,
            src: [
              'u2f-api.js'
            ],
            dest: 'assets'
          }
        ]
      }
    }
  });

  grunt.loadNpmTasks('grunt-contrib-copy');
}
