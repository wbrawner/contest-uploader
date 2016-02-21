<?php
/*
Plugin Name: Xubuntu Wallpaper Contest Submission Uploader
Plugin URI: http://wbrawner.com/
Description: A simple plugin that allows users to upload images to a public gallery for the wallpaper contest. To use it, simply enable the plugin and place the [contest_submissions] shortcode on the page/post that you'd like to see the entries on. Once your users have uploaded photos, be sure to approve them or deny them and give them feedback!
Tags: xubuntu, image upload, contest
Version: 1.0
Author: William Brawner (Branau)
Author URI: http://wbrawner.com
License: GPL2
Copyright 2015 William Brawner (email : billybrawner@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

function contest_uploader_init() {
    require( dirname( __FILE__ ) . '/contest-uploader-functions.php' );
}
add_action( 'init', 'contest_uploader_init' );
