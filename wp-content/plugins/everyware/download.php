<?php

/*
 * Takes uuid, type and filename and returns a file.
 */
if ( isset( $_GET['uuid'] ) ) {
	
	define( 'WP_USE_THEMES', false );
	require( '../../../wp-blog-header.php' );

	$uuid       = $_GET['uuid'];
	$related	= isset( $_GET['related'] ) && $_GET['related'] === 'true' ? true : false;
	
	$oc_api 		= new OcApi();
	$base_url 		= $oc_api->getOcUrl();
	$article_data 	= $oc_api->get_url_content( $base_url . 'objects/' . $uuid . '/download');
	header( 'HTTP/1.0 200 OK' );

	if( isset( $article_data ) && $article_data !== '401' ) {
		
		$article = $oc_api->get_single_article( $uuid );
		$article_images = $article['article_images'];
				
		$name = '';
		if( isset( $article['article']->headline[0] ) ) {
			$name = $article['article']->headline[0];
		}
		else if( isset( $article['article']->text[0] ) ) {
			$name = OcUtilities::truncate_article_text( $article['article']->text[0], 50 );
		}
		$section = isset( $article['article']->shopsection[0] ) ? $article['article']->shopsection[0] : '';

		$file_extension 		= substr( $article_disp, $file_extension_ind );
		if( $article['article']->metadata_mimetype[0] === 'application/vnd.infomaker.se-npexchange.article' ) {
			$file_extension 	= '.npdoc' ;
		}
		else if( $article['article']->metadata_mimetype[0] === 'saxo/article' ) {
			$file_extension 	= '.xml' ;
		}

		$article_disp 			= $article_data['headers']['content-disposition'];
		$file_extension_ind		= strrpos( $article_disp, '.' );
		$article_filename 		= $name . $file_extension;
		$article_filename_clean = $name . '_clean.txt';

		$zipname = date( 'Ymd_His' ) . '_' . $name . '.zip';
		$zip = new ZipArchive;
		$zip_path = WP_PLUGIN_DIR . '/Every/' . $zipname;
		$res = $zip->open( $zip_path, ZipArchive::CREATE );
		if( $res === true ) {

			if( $related ) {
				$download_uuids = array();
				$relation_data 	= $oc_api->get_url_content( $base_url . 'objects/' . $uuid . '/relations');

				if( isset( $relation_data ) && $relation_data !== '401' ) {

					$relation_data = wp_remote_retrieve_body( $relation_data );
					$relation_data = json_decode( $relation_data );
					foreach ( $relation_data->relations as $relation ) {
						foreach ( $relation->uuids as $relation_uuid ) {
							if( isset( $relation_uuid ) ) {
								array_push( $download_uuids, $relation_uuid );
							}
						}
					}
				}

				foreach ( $download_uuids as $download_uuid ) {
					
					$file_data = $oc_api->get_url_content( $base_url . 'objects/' . $download_uuid . '/download');
					$file_name = $file_data['headers']['content-disposition'];
					$file_index = strrpos( $file_name, 'filename=' );
					$file_name = substr( $file_name, $file_index + 9 );
					$file_data = wp_remote_retrieve_body( $file_data );
					$zip->addFromString( $file_name, $file_data );
				}
			}

			$reports_file = plugin_dir_path( __FILE__ ) . '../EveryContentShop/reports/reports.php';
			if( file_exists( $reports_file ) ) {
				include_once $reports_file;

				if( !EveryContentShop_Reports::ecs_reports_insert( $article['article']->organization[0], $name, $uuid, '100', $section ) ) {
					header( 'HTTP/1.0 200 OK' );
					print __("Could not register download, please try again", "every");
					return;
				}
			}		
			
			$zip->addFromString( $article_filename, wp_remote_retrieve_body( $article_data ) );
			$zip->addFromString( $article_filename_clean, clean_article_str( $article['article'], $article_images ) );

			if( $zip->close() ) {
				header( 'HTTP/1.0 200 OK' );
				header( 'Content-Type: application/zip' );
				header( 'Content-disposition: attachment; filename="' . $zipname . '"' );
				header( 'Content-Length: ' . filesize($zip_path) );

				ob_clean();
		    	flush();
				print readfile( $zip_path );
				unlink( $zip_path ); 
			}
			else {
				print __('Could not write to zip file.', 'every');
			}
		}
		else {
			print __('Zip failed: ', 'every') . $res;
		}
	}
}

function clean_article_str( $article, $article_image_uuids ) {
	
	$oc_api 		= new OcApi();
	$article_str 	= '';

	if( isset( $article->headline[0] ) ) {
		$article_str .= strip_tags( trim( $article->headline[0] ) ) . "\n\n";
	}

	if( isset( $article->leadin[0] ) ) {
		$article_str .= strip_tags( trim( $article->leadin[0] ) ) . "\n\n";
	}

	if( isset( $article->text[0] ) ) {
		$article_str .= strip_tags( trim( $article->text[0] ) ) . "\n\n";
	}

	if( isset( $article->factheadline, $article->factbody ) ) {
		$fact_headline_arr = $article->factheadline;
		$fact_body_arr     = $article->factbody;
		for ( $i = 0; $i < count( $fact_headline_arr ); $i ++ ) {
			$article_str .= strip_tags( trim( $fact_headline_arr[$i] ) ) . "\n\n";
			$article_str .= strip_tags( trim( $fact_body_arr[$i] ) ) . "\n\n";
		}
	}

	if( isset( $article->author[0] ) ) {
		$article_str .= strip_tags( trim( $article->author[0] ) ) . "\n\n";
	}

	if( count( $article_image_uuids ) > 0 ) {

		foreach( $article_image_uuids as $img_uuid ) {

			$img_data = $oc_api->get_single_image_metadata( $img_uuid );
			
			if( isset( $img_data->filename[0] ) ) {
				$article_str .= strip_tags( trim( $img_data->filename[0] ) ) . "\n";
			}

			if( isset( $img_data->description[0] ) ) {
				$article_str .= strip_tags( trim( $img_data->description[0] ) ) . "\n";
			}

			if( isset( $img_data->photographer[0] ) ) {
				$article_str .= strip_tags( trim( $img_data->photographer[0] ) ) . "\n\n";
			}
		}
	}

	return $article_str;
}