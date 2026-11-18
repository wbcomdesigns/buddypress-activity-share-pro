#!/usr/bin/env node
/**
 * Builds assets/css/*.min.css, *-rtl.css, *-rtl.min.css and assets/js/*.min.js.
 * Naming matches wp_style_add_data( 'rtl', 'replace' ) + 'suffix' => '.min'.
 */
const fs = require( 'fs' );
const path = require( 'path' );
const rtlcss = require( 'rtlcss' );
const CleanCSS = require( 'clean-css' );
const { minify } = require( 'terser' );

const root = path.resolve( __dirname, '..' );
const cssDir = path.join( root, 'assets/css' );
const jsDir = path.join( root, 'assets/js' );
const isSource = ( f, ext ) => f.endsWith( ext ) && ! f.includes( '.min.' ) && ! f.endsWith( '-rtl' + ext );
const clean = new CleanCSS( { level: 1 } );

( async () => {
	for ( const file of fs.readdirSync( cssDir ).filter( ( f ) => isSource( f, '.css' ) ) ) {
		const base = file.slice( 0, -4 );
		const css = fs.readFileSync( path.join( cssDir, file ), 'utf8' );
		const rtl = rtlcss.process( css );
		fs.writeFileSync( path.join( cssDir, `${ base }-rtl.css` ), rtl );
		fs.writeFileSync( path.join( cssDir, `${ base }.min.css` ), clean.minify( css ).styles );
		fs.writeFileSync( path.join( cssDir, `${ base }-rtl.min.css` ), clean.minify( rtl ).styles );
		console.log( `css  ${ file }` );
	}
	for ( const file of fs.readdirSync( jsDir ).filter( ( f ) => isSource( f, '.js' ) ) ) {
		const out = await minify( fs.readFileSync( path.join( jsDir, file ), 'utf8' ), { compress: true, mangle: true } );
		fs.writeFileSync( path.join( jsDir, file.replace( /\.js$/, '.min.js' ) ), out.code );
		console.log( `js   ${ file }` );
	}
} )().catch( ( e ) => {
	console.error( e );
	process.exit( 1 );
} );
