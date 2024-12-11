<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Front extends My_Controller 
		{
	
		
			
	  	public function __construct()
				{
					parent::__construct();
						$session = array("isshow"=>0);
							$this->session->set_userdata($session);
				}
		
			public function BannerDesktopBigiframe()
				{
					$this->load->view("widgets/desktop-big.php");				
				}
		
			public function BannerDesktopSmalliframe()
				{
					$this->load->view("widgets/desktop-small.php");				
				}
				
			public function FloatingDesktop()
				{
					?>
						<center>
							<img src="/ipl-2021/assets/kt-main.png" style="min-width:1090px; max-width:100%;" />
						</center>
						
						<iframe frameborder="0" marginheight="0" marginwidth="0" scrolling="no" style="border: 0px; min-width: 1px; overflow: hidden !important; display: block; clear: both; width: 10em; height: 9em; margin: 0 4em 4em 0; perspective: 400px; position: fixed; bottom: 0px; right: 0;" src="https://cricket.khaleejtimes.com/ipl-2021/website/front/FloatingDesktopIframe?embed=true"></iframe>
					<?php
				}
				
			public function FloatingDesktopIframe()
				{
					?>
						<link rel="stylesheet" type="text/css" href="/ipl-2020/entitysport.css?v=2.11" />
						<link href="/ipl-2021/themes/frontend/css/font-awesome.css" rel="stylesheet" />
						<script src="https://cricket.khaleejtimes.com/ipl-2021/themes/frontend/js/jquery.js"></script>
							<script> var Entity_sport = []; </script>
						
						<div id="bottomwidgethere" class="bottomwidgethere" matchid="<?php $path = str_replace("ipl-2021/","",FCPATH); echo $match_id = file_get_contents($path."matchcentre/matchid.txt"); ?>"></div>
						
							<style>
								.entity-cricket-bottom-widget { margin: 0px; bottom: 0px; }1
							</style>
						
						<script defer src="https://dashboard.entitysport.com/widget/assets/js/widget.js?v=2.11"></script>
						<script src="/ipl-2021/entitysport.js?v=2.11"></script>
						

						<script>
								$("a").attr("target","_BLANK");
								setInterval(function(){
									$("a").attr("target","_BLANK");
								},1000);
						</script>
					<?php
				}
				
			public function BannerDesktopSmall()
				{
					?>
						<center>
							<iframe frameborder="0" marginheight="0" marginwidth="0" scrolling="no" style="border:
0px; height: 340px; min-width: 1px; width: 1140px; overflow: hidden !important; margin: 0px auto; display: block;clear:both;" src="https://cricket.khaleejtimes.com/ipl-2021/website/front/BannerDesktopSmalliframe?embed=true"></iframe>
						</center>
					<?php				
				}
				
			public function BannerDesktopBig()
				{
					?>
						<center>
							<iframe frameborder="0" marginheight="0" marginwidth="0" scrolling="no" style="border:
0px; height: 660px; min-width: 1px; width: 1140px; overflow: hidden !important; margin: 0px auto; display: block;clear:both;" src="https://cricket.khaleejtimes.com/ipl-2021/website/front/BannerDesktopBigiframe?embed=true"></iframe>
						</center>
					<?php				
				}
				
			public function BannerMobile()
				{
					?>
						<center>
							<iframe frameborder="0" marginheight="0" marginwidth="0" scrolling="no" style="border:
0px; height: 160px; min-width: 1px; width: 360px; overflow: hidden !important; margin: 0px auto; display: block;clear:both;" src="https://cricket.khaleejtimes.com/ipl-2021/cricketbanner?embed=true"></iframe>
						</center>
					<?php					
				}
				
			public function footerwidget()
				{
					$this->load->view("front/inc/mc_footerwidget.php");				
				}
				
			public function index()
				{
					$data = array();
					$seo = array();
						$seo['url']				=	site_url("home");
						$seo['title']			=	WEBSITENAME;
						$seo['metatitle']		=	WEBSITENAME;
						$seo['metadescription']	=	WEBSITENAME;
								$data['data']['seo']	=	$seo;

							$currentime	=	getgmttime();
									// 
									$sortnew	=	"
														(
																date(newsdate) <= '$currentime' 
															AND
																news.status = '1'
														)
													";

					$orderby	=	"news.trending ASC";
					$orderby	=	"news.newsdate DESC";

						//	$currentime = 1;

							$trendingsql	=	"
													SELECT 
														news.title, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													LEFT JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'normal'
													AND
														 $sortnew
													GROUP BY news.Id 
													ORDER BY $orderby
													LIMIT 8
												";
										$trendingdata = $this->db->query($trendingsql);		
											$data['trendingdata'] = $trendingdata->result_array();
												 
							$trendingsql2	=	"
													SELECT 
														news.title, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													INNER JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'normal'
													AND
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby
													LIMIT 8,6
												";
										$trendingdata2 = $this->db->query($trendingsql2);		
											$data['trendingdata2'] = $trendingdata2->result_array();
													 
							 $gallerysql	=	"
													SELECT 
														news.title, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													LEFT JOIN news_cat on news_cat.news_id = news.Id 
													LEFT JOIN categroy on categroy.cat_id = news_cat.cat_id 
													INNER JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'gallery'
													AND
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby 
													LIMIT 6
												";
										$gallerydata = $this->db->query($gallerysql);		
											$data['gallerydata'] = $gallerydata->result_array();

							$videosql	=	"
													SELECT 
														news.title, news.newsdate, news.video, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													INNER JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'video'
													AND
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby 
													LIMIT 6
												";
												if(isset($_GET['dev'])) echo $videosql;
										$videodata = $this->db->query($videosql);		
											$data['videodata'] = $videodata->result_array();
												 		 
							$latestsql	=	"
													SELECT 
														news.title,news.newstype, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													INNER JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby 
													LIMIT 6
												";
										$latestdata = $this->db->query($latestsql);		
											$data['latestdata'] = $latestdata->result_array();
												 

									$data['layout'] = $this->webLayout($data);
	                    $this->load->view('front/index.php',$data);
				}
				
			public function index5()
				{
					$data = array();
					$seo = array();
						$seo['url']				=	site_url("home");
						$seo['title']			=	WEBSITENAME;
						$seo['metatitle']		=	WEBSITENAME;
						$seo['metadescription']	=	WEBSITENAME;
								$data['data']['seo']	=	$seo;

							$currentime	=	getgmttime();
									// 
									$sortnew	=	"
														(
																date(newsdate) <= '$currentime' 
															AND
																news.status = '1'
														)
													";

					$orderby	=	"news.trending ASC";
					$orderby	=	"news.newsdate DESC";

						//	$currentime = 1;

							$trendingsql	=	"
													SELECT 
														news.title,news.hits, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													LEFT JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'normal'
													AND
														 $sortnew
													GROUP BY news.Id 
													ORDER BY $orderby
													LIMIT 8
												";
												
										$trendingsql	=	"
													SELECT 
														news.title,news.hits, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source,  categroy.cat_title 
													FROM 
														news 
													LEFT JOIN news_cat on news_cat.news_id = news.Id 
													LEFT JOIN categroy on categroy.cat_id = news_cat.cat_id 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'normal'
													AND
														 $sortnew
													GROUP BY news.Id 
													ORDER BY $orderby
													LIMIT 8
												";		
												
										$trendingdata = $this->db->query($trendingsql);		
											$data['trendingdata'] = $trendingdata->result_array();
												 
							$trendingsql2	=	"
													SELECT 
														news.title,news.hits, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													LEFT JOIN news_cat on news_cat.news_id = news.Id 
													LEFT JOIN categroy on categroy.cat_id = news_cat.cat_id 
													LEFT JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'normal'
													AND
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby
													LIMIT 8,18
												";
										$trendingdata2 = $this->db->query($trendingsql2);		
											$data['trendingdata2'] = $trendingdata2->result_array();
													 
							 $gallerysql	=	"
													SELECT 
														news.title, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													LEFT JOIN news_cat on news_cat.news_id = news.Id 
													LEFT JOIN categroy on categroy.cat_id = news_cat.cat_id 
													LEFT JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'gallery'
													AND
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby 
													LIMIT 6
												";
										$gallerydata = $this->db->query($gallerysql);		
											$data['gallerydata'] = $gallerydata->result_array();

							$videosql	=	"
													SELECT 
														news.title, news.newsdate, news.video, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													LEFT JOIN news_cat on news_cat.news_id = news.Id 
													LEFT JOIN categroy on categroy.cat_id = news_cat.cat_id 
													LEFT JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'video'
													AND
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby 
													LIMIT 6
												";
												// if(isset($_GET['dev'])) echo $videosql;
										$videodata = $this->db->query($videosql);		
											$data['videodata'] = $videodata->result_array();
												 		 
							$latestsql	=	"
													SELECT 
														news.title,news.hits,news.newstype, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													LEFT JOIN news_cat on news_cat.news_id = news.Id 
													LEFT JOIN categroy on categroy.cat_id = news_cat.cat_id 
													LEFT JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby 
													LIMIT 26,21
												";
										$latestdata = $this->db->query($latestsql);		
											$data['latestdata'] = $latestdata->result_array();
												 

									$data['isloadtop'] = 0; 
									$data['layout'] = $this->webLayout($data);
						
					$dev  = 5;
						if(isset($_GET['dev']))
							{
								$dev  = $_GET['dev'];
							}
							
						if(isset($_GET['castrol']))
							{
									$this->session->set_userdata(array('castrol'=>'castrol'));
							}
						if(isset($_GET['abevia']))
							{
									$this->session->set_userdata(array('abevia'=>'abevia'));
							}
							
						if(isset($_GET['castrol2']))
							{
									$this->session->set_userdata(array('castrol2'=>'castrol2'));
							}
							
							
						$castrol = $this->session->userdata('castrol');
							if(!empty($castrol))
								{
									$dev  = "-castrol";
								}
								
						$castrol2 = $this->session->userdata('castrol2');
							if(!empty($castrol2))
								{
									$dev  = "-castrol2";
								}
								
						$abevia = $this->session->userdata('abevia');
							if(!empty($abevia))
								{
									$dev  = "-abevia";
								}
						//	echo "# $dev #";
					$this->load->view("front/index$dev.php",$data); 
				}
				
			public function dsoa()
				{
					$data = array();
					$seo = array();
						$seo['url']				=	site_url("home");
						$seo['title']			=	WEBSITENAME;
						$seo['metatitle']		=	WEBSITENAME;
						$seo['metadescription']	=	WEBSITENAME;
								$data['data']['seo']	=	$seo;

							$currentime	=	getgmttime();
									// 
									$sortnew	=	"
														(
																date(newsdate) <= '$currentime' 
															AND
																news.status = '1'
														)
													";

					$orderby	=	"news.trending ASC";
					$orderby	=	"news.newsdate DESC";

						//	$currentime = 1;

							$trendingsql	=	"
													SELECT 
														news.title, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													LEFT JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'normal'
													AND
														 $sortnew
													GROUP BY news.Id 
													ORDER BY $orderby
													LIMIT 8
												";
										$trendingdata = $this->db->query($trendingsql);		
											$data['trendingdata'] = $trendingdata->result_array();
												 
							$trendingsql2	=	"
													SELECT 
														news.title, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													INNER JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'normal'
													AND
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby
													LIMIT 8,6
												";
										$trendingdata2 = $this->db->query($trendingsql2);		
											$data['trendingdata2'] = $trendingdata2->result_array();
													 
							 $gallerysql	=	"
													SELECT 
														news.title, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													LEFT JOIN news_cat on news_cat.news_id = news.Id 
													LEFT JOIN categroy on categroy.cat_id = news_cat.cat_id 
													INNER JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'gallery'
													AND
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby 
													LIMIT 6
												";
										$gallerydata = $this->db->query($gallerysql);		
											$data['gallerydata'] = $gallerydata->result_array();

							$videosql	=	"
													SELECT 
														news.title, news.newsdate, news.video, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													INNER JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'video'
													AND
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby 
													LIMIT 6
												";
												// if(isset($_GET['dev'])) echo $videosql;
										$videodata = $this->db->query($videosql);		
											$data['videodata'] = $videodata->result_array();
												 		 
							$latestsql	=	"
													SELECT 
														news.title,news.newstype, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													INNER JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby 
													LIMIT 6
												";
										$latestdata = $this->db->query($latestsql);		
											$data['latestdata'] = $latestdata->result_array();
												 

									$data['isloadtop'] = 0; 
									$data['layout'] = $this->webLayout($data);
	                    $this->load->view('front/dsoa.php',$data); 
				}
				
			public function dsoa2()
				{
					$data = array();
					$seo = array();
						$seo['url']				=	site_url("home");
						$seo['title']			=	WEBSITENAME;
						$seo['metatitle']		=	WEBSITENAME;
						$seo['metadescription']	=	WEBSITENAME;
								$data['data']['seo']	=	$seo;

							$currentime	=	getgmttime();
									// 
									$sortnew	=	"
														(
																date(newsdate) <= '$currentime' 
															AND
																news.status = '1'
														)
													";

					$orderby	=	"news.trending ASC";
					$orderby	=	"news.newsdate DESC";

						//	$currentime = 1;

							$trendingsql	=	"
													SELECT 
														news.title, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													LEFT JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'normal'
													AND
														 $sortnew
													GROUP BY news.Id 
													ORDER BY $orderby
													LIMIT 8
												";
										$trendingdata = $this->db->query($trendingsql);		
											$data['trendingdata'] = $trendingdata->result_array();
												 
							$trendingsql2	=	"
													SELECT 
														news.title, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													INNER JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'normal'
													AND
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby
													LIMIT 8,6
												";
										$trendingdata2 = $this->db->query($trendingsql2);		
											$data['trendingdata2'] = $trendingdata2->result_array();
													 
							 $gallerysql	=	"
													SELECT 
														news.title, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													LEFT JOIN news_cat on news_cat.news_id = news.Id 
													LEFT JOIN categroy on categroy.cat_id = news_cat.cat_id 
													INNER JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'gallery'
													AND
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby 
													LIMIT 6
												";
										$gallerydata = $this->db->query($gallerysql);		
											$data['gallerydata'] = $gallerydata->result_array();

							$videosql	=	"
													SELECT 
														news.title, news.newsdate, news.video, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													INNER JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'video'
													AND
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby 
													LIMIT 6
												";
												// if(isset($_GET['dev'])) echo $videosql;
										$videodata = $this->db->query($videosql);		
											$data['videodata'] = $videodata->result_array();
												 		 
							$latestsql	=	"
													SELECT 
														news.title,news.newstype, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													INNER JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby 
													LIMIT 6
												";
										$latestdata = $this->db->query($latestsql);		
											$data['latestdata'] = $latestdata->result_array();
												 

									$data['isloadtop'] = 0; 
									$data['layout'] = $this->webLayout($data);
	                    $this->load->view('front/dsoa2.php',$data); 
				}

			public function index6()
				{
					$data = array();
					$seo = array();
						$seo['url']				=	site_url("home");
						$seo['title']			=	WEBSITENAME;
						$seo['metatitle']		=	WEBSITENAME;
						$seo['metadescription']	=	WEBSITENAME;
								$data['data']['seo']	=	$seo;

							$currentime	=	getgmttime();
									// 
									$sortnew	=	"
														(
																date(newsdate) <= '$currentime' 
															AND
																news.status = '1'
														)
													";

					$orderby	=	"news.trending ASC";
					$orderby	=	"news.newsdate DESC";

						//	$currentime = 1;

							$trendingsql	=	"
													SELECT 
														news.title, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													LEFT JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'normal'
													AND
														 $sortnew
													GROUP BY news.Id 
													ORDER BY $orderby
													LIMIT 8
												";
										$trendingdata = $this->db->query($trendingsql);		
											$data['trendingdata'] = $trendingdata->result_array();
												 
							$trendingsql2	=	"
													SELECT 
														news.title, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													INNER JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'normal'
													AND
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby
													LIMIT 8,6
												";
										$trendingdata2 = $this->db->query($trendingsql2);		
											$data['trendingdata2'] = $trendingdata2->result_array();
													 
							 $gallerysql	=	"
													SELECT 
														news.title, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													LEFT JOIN news_cat on news_cat.news_id = news.Id 
													LEFT JOIN categroy on categroy.cat_id = news_cat.cat_id 
													INNER JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'gallery'
													AND
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby 
													LIMIT 6
												";
										$gallerydata = $this->db->query($gallerysql);		
											$data['gallerydata'] = $gallerydata->result_array();

							$videosql	=	"
													SELECT 
														news.title, news.newsdate, news.video, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													INNER JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'video'
													AND
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby 
													LIMIT 6
												";
												// if(isset($_GET['dev'])) echo $videosql;
										$videodata = $this->db->query($videosql);		
											$data['videodata'] = $videodata->result_array();
												 		 
							$latestsql	=	"
													SELECT 
														news.title,news.newstype, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													INNER JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby 
													LIMIT 6
												";
										$latestdata = $this->db->query($latestsql);
											$data['latestdata'] = $latestdata->result_array();
								//	$data['isloadtop'] = 1; 
									$data['hideexitwidget'] = 1;
									$data['layout'] = $this->webLayout($data);
	                    $this->load->view('front/index6.php',$data); 
				}

				
			public function index3()
				{
					$data = array();
					$seo = array();
						$seo['url']				=	site_url("home");
						$seo['title']			=	WEBSITENAME;
						$seo['metatitle']		=	WEBSITENAME;
						$seo['metadescription']	=	WEBSITENAME;
								$data['data']['seo']	=	$seo;

							$currentime	=	getgmttime();
									// 
									$sortnew	=	"
														(
																date(newsdate) <= '$currentime' 
															AND
																news.status = '1'
														)
													";

					$orderby	=	"news.trending ASC";
					$orderby	=	"news.newsdate DESC";

						//	$currentime = 1;

							$trendingsql	=	"
													SELECT 
														news.title, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													LEFT JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'normal'
													AND
														 $sortnew
													GROUP BY news.Id 
													ORDER BY $orderby
													LIMIT 8
												";
										$trendingdata = $this->db->query($trendingsql);		
											$data['trendingdata'] = $trendingdata->result_array();
												 
							$trendingsql2	=	"
													SELECT 
														news.title, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													INNER JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'normal'
													AND
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby
													LIMIT 8,6
												";
										$trendingdata2 = $this->db->query($trendingsql2);		
											$data['trendingdata2'] = $trendingdata2->result_array();
													 
							 $gallerysql	=	"
													SELECT 
														news.title, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													LEFT JOIN news_cat on news_cat.news_id = news.Id 
													LEFT JOIN categroy on categroy.cat_id = news_cat.cat_id 
													INNER JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'gallery'
													AND
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby 
													LIMIT 6
												";
										$gallerydata = $this->db->query($gallerysql);		
											$data['gallerydata'] = $gallerydata->result_array();

							$videosql	=	"
													SELECT 
														news.title, news.newsdate, news.video, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													INNER JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'video'
													AND
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby 
													LIMIT 6
												";
												if(isset($_GET['dev'])) echo $videosql;
										$videodata = $this->db->query($videosql);		
											$data['videodata'] = $videodata->result_array();
												 		 
							$latestsql	=	"
													SELECT 
														news.title,news.newstype, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													INNER JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby 
													LIMIT 6
												";
										$latestdata = $this->db->query($latestsql);		
											$data['latestdata'] = $latestdata->result_array();
												 

									$data['layout'] = $this->webLayout($data);
	                    $this->load->view('front/index3.php',$data);
				}

			public function index2()
				{
					$data = array();
					$seo = array();
						$seo['url']				=	site_url("home");
						$seo['title']			=	WEBSITENAME;
						$seo['metatitle']		=	WEBSITENAME;
						$seo['metadescription']	=	WEBSITENAME;
								$data['data']['seo']	=	$seo;

							$currentime	=	getgmttime();
									// 
									$sortnew	=	"
														(
																date(newsdate) <= '$currentime' 
															AND
																news.status = '1'
														)
													";
							
					$orderby	=	"news.trending ASC";
					$orderby	=	"news.newsdate DESC";
							
						//	$currentime = 1;

							$trendingsql	=	"
													SELECT 
														news.title, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													LEFT JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'normal'
													AND
														 $sortnew
													GROUP BY news.Id 
													ORDER BY $orderby
													LIMIT 8
												";
										$trendingdata = $this->db->query($trendingsql);		
											$data['trendingdata'] = $trendingdata->result_array();
												 
							$trendingsql2	=	"
													SELECT 
														news.title, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													INNER JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'normal'
													AND
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby
													LIMIT 8,6
												";
										$trendingdata2 = $this->db->query($trendingsql2);		
											$data['trendingdata2'] = $trendingdata2->result_array();
													 
							 $gallerysql	=	"
													SELECT 
														news.title, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													INNER JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'gallery'
													AND
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby 
													LIMIT 6
												";
										$gallerydata = $this->db->query($gallerysql);		
											$data['gallerydata'] = $gallerydata->result_array();

							$videosql	=	"
													SELECT 
														news.title, news.newsdate, news.video, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													INNER JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'video'
													AND
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby 
													LIMIT 6
												";
												if(isset($_GET['dev'])) echo $videosql;
										$videodata = $this->db->query($videosql);		
											$data['videodata'] = $videodata->result_array();
												 		 
							$latestsql	=	"
													SELECT 
														news.title,news.newstype, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													INNER JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														$sortnew
													GROUP BY news.Id 
													ORDER BY $orderby 
													LIMIT 6
												";
										$latestdata = $this->db->query($latestsql);		
											$data['latestdata'] = $latestdata->result_array();
												 

									$data['layout'] = $this->webLayout($data);
	                    $this->load->view('front/index2.php',$data);
				}
				
			
			public function getnewsurl($newid)
				{
					
						$session = array("isshow"=>1);
								$this->session->set_userdata($session);
					 
						$sortnew	=	"
											(
													news.status = '1'
											)
										";
					$sql	=	"
													SELECT 
														news.hits, news.title, news.metatitle, news.newsdate, news.newstype, news.metadescription, news.Id , news.alias, news.video, news.description, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													LEFT JOIN news_cat on news_cat.news_id = news.Id 
													LEFT JOIN categroy on categroy.cat_id = news_cat.cat_id 
													LEFT JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														$sortnew
													AND
														`news`.`Id`  = $newid
													";   // exit($sql); 
										$sql = $this->db->query($sql);		
										$temp = $sql->result_array();
										$data['singlenews'] = $temp;
												if(empty($temp))
													{
														redirect(site_url('home'));
													}
						$alias	=	generateSeoURL($temp[0]['title'],50);	
							$url	=	site_url("news")."/$alias/$newid";
								redirect($url);
				}
				 
			public function searchfornews() 
				{
					$data = array();
					
						$seo['url']				=	site_url("search");
										$seo['img']				=	base_url("themes/frontend/images/logo.png");
										$seo['title']			=	"Search";
										$seo['metatitle']		=	"Search";
										$seo['metadescription']	=	"Search";
										$data['seo']	=	$seo;
													
										
							$data['layout'] = $this->webLayout($data);
					
					$this->load->view("front/news/newssearch.php",$data);
				}
				
			public function getnewsdescription($newid)
				{ 	 
					$sql	=	"
													SELECT 
														news.description 
													FROM 
														news 
													LEFT JOIN news_cat on news_cat.news_id = news.Id 
													LEFT JOIN categroy on categroy.cat_id = news_cat.cat_id 
													LEFT JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`Id`  = $newid
													";   // exit($sql); 
										$sql = $this->db->query($sql);		
										$temp = $sql->result_array();
										$singlenews = $temp;
												if(empty($temp))
													{
														redirect(site_url('home'));
													}
													$singlenews = $singlenews[0];
													
												
					$showhtml =	 str_replace("../uploads/incontent",base_url("uploads/incontent"),$singlenews['description']);
					
						?>
						<!--link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous" /--> 
										<style>	
											body {  overflow-y: hidden; }
											.newshtmlcontent { color: #231f20; font-size: 17px; line-height: 26px;    letter-spacing: 0.2px; font-family: 'Open Sans', sans-serif; }
											.newshtmlcontent img { max-width: 100%;  height: auto !important; }
											
										</style>
						
						<?php
					echo "<div class='newshtmlcontent'>";
						echo $showhtml = str_replace(".SandboxRoot { display: none; max-height: 10000px; }","",$showhtml);	
					echo "</div>";	
				}
				
			public function getcaturl($catid)
				{
					
					$q = $this->input->get("q");
						
						if(!empty($q))
							{
								$q = "?q=$q";
							}
					
					$sql	=	"
													SELECT 
														*
													FROM 
														categroy 
													WHERE
														status = '1'
													AND
														cat_id  = '$catid'
													";    // exit($sql); 
										$sql = $this->db->query($sql);		
										$temp = $sql->result_array();
										
												if(empty($temp))
													{
														redirect(site_url('home'));
													}
						$alias	=	generateSeoURL($temp[0]['cat_title'],50);	
							$url	=	site_url("category")."/$alias/$catid/1$q";
								redirect($url);
				}
				
				
			public function news_details($newid)
				{
						$session = array("isshow"=>1);
								$this->session->set_userdata($session);
					$data = array();
					$seo = array();
				
						$sortnew	=	"
											(
													news.status = '1'
											)
										";
										
												$sql	=	"
																UPDATE `news` SET hits = ( hits+1 ) where Id = '$newid';
															";
																$this->db->query($sql);
										
									$sql	=	"
													SELECT 
														`news`.`media_url`,`news`.`writerid`,categroy.cat_id,news.hits, news.title, news.metatitle, news.newsdate, news.newstype, news.metadescription, news.Id , news.alias, news.video, news.description, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													LEFT JOIN news_cat on news_cat.news_id = news.Id 
													LEFT JOIN categroy on categroy.cat_id = news_cat.cat_id 
													LEFT JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														$sortnew
													AND
														`news`.`Id`  = $newid
													";   // exit($sql); 
										$sql = $this->db->query($sql);		
										$temp = $sql->result_array();
										$data['singlenews'] = $temp;
												if(empty($temp))
													{
														redirect(site_url('home'));
													}

													$Id = $temp[0]['Id'];
													$newstype = $temp[0]['newstype'];
													
													
													$seoimg = base_url("themes/frontend/images/logo.png");
													
													
															$seoimg	= base_url("uploads/uploads/".$temp[0]['url']);
																	if($temp[0]['source']=='feed')
																		$seoimg	= $temp[0]['url'];
													
													$alias = generateSeoURL($temp[0]['title'],50);	
										$seo['url']				=	site_url("news")."/$alias/$Id";
										$seo['ampurl']				=	site_url("amp/news")."/$alias/$Id";
										$seo['img']				=	$seoimg;
										$seo['title']			=	$temp[0]['title'];
										$seo['metatitle']		=	$temp[0]['metatitle'];
										$seo['metadescription']	=	$temp[0]['metadescription'];
										$data['seo']	=	$seo;
													
										
							$data['layout'] = $this->webLayout($data);
							
									if($newstype=="video")
										{
											$data['hideexitwidget']	=	1;
										}

								switch($newstype)
									{
										case "gallery":
											$this->load->view('front/news/gallery_details.php',$data);
										break;
										default:
												if(isset($_GET['dev']))
													$this->load->view('front/news/news_details_new.php',$data);
												else
													$this->load->view('front/news/news_details.php',$data);
										break;
									}
							
	    		}



				
			public function amp_news_details($newid)
				{
					
					$data = array();
					$seo = array();
				
						$sortnew	=	"
											(
													news.status = '1'
											)
										";
										
												$sql	=	"
																UPDATE `news` SET hits = ( hits+1 ) where Id = '$newid';
															";
																$this->db->query($sql);
										
									$sql	=	"
													SELECT 
														`news`.`media_url`,categroy.cat_id,news.hits, news.title, news.metatitle, news.newsdate, news.newstype, news.metadescription, news.Id , news.alias, news.video, news.description, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													LEFT JOIN news_cat on news_cat.news_id = news.Id 
													LEFT JOIN categroy on categroy.cat_id = news_cat.cat_id 
													LEFT JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														$sortnew
													AND
														`news`.`Id`  = $newid
													";   // exit($sql); 
										$sql = $this->db->query($sql);		
										$temp = $sql->result_array();
										$data['singlenews'] = $temp;
												if(empty($temp))
													{
														redirect(site_url('home'));
													}

													$Id = $temp[0]['Id'];
													$newstype = $temp[0]['newstype'];
													
													
													$seoimg = base_url("themes/frontend/images/logo.png");
													
													
															$seoimg	= base_url("uploads/uploads/".$temp[0]['url']);
																	if($temp[0]['source']=='feed')
																		$seoimg	= $temp[0]['url'];
													
													$alias = generateSeoURL($temp[0]['title'],50);	
										$seo['url']				=	site_url("news")."/$alias/$Id";
										$seo['ampurl']				=	site_url("amp/news")."/$alias/$Id";
										$seo['img']				=	$seoimg;
										$seo['title']			=	$temp[0]['title'];
										$seo['metatitle']		=	$temp[0]['metatitle'];
										$seo['metadescription']	=	$temp[0]['metadescription'];
										$data['seo']	=	$seo;
													
										
							$data['layout'] = $this->webLayout($data);
							
									if($newstype=="video")
										{
											$data['hideexitwidget']	=	1;
										}

								$this->load->view('front/news/amp.php',$data);
							
	    		}				
				
			public function Fixture()
				{
					$data	=	array();
					$seo	=	array();
						$data['layout'] = $this->webLayout($data);
							$this->load->view('front/news/fixers.php',$data);
	    		}	
				
			public function results()
				{
					$data = array();
					$seo = array();
				
													
										
							$data['layout'] = $this->webLayout($data);
							$this->load->view('front/news/result.php',$data);
	    		}
			public function matchcentre()
				{
					$data = array();
					$seo = array();
				
													
										
							$data['layout'] = $this->webLayout($data);
							$this->load->view('front/news/matchcentre.php',$data);
	    		}

			public function ikea()
				{
					$data = array();
					$seo = array();
				
													
										
							$data['layout'] = $this->webLayout($data);
							$this->load->view('front/ikea/index.php',$data);
	    		}
				
			public function viewiframes($value="matchcentre")
				{
					$data = array();
					$seo = array();					
						$data['layout'] = $this->webLayout($data);
							$data['value'] = $value;
							
								if("fixture"==$value)
									{
										//$this->load->view('front/cricketbanner/staticfixture.php',$data);
										//return "";
									}
									
								if("matchcentre2"==$value)
									{
										$data['value'] = "matchcentre";
										$this->load->view('front/news/viewiframes2.php',$data);
									} else {
										$this->load->view('front/news/viewiframes2.php',$data);
									}
	    		}
				
			public function ranking()
				{
					$data = array();
					$seo = array();
				
													
										
							$data['layout'] = $this->webLayout($data);
							$this->load->view('front/news/ranking.php',$data);
	    		}	
				
				
			public function news()
				{
					$data = array();
					$seo = array();
						$seo['url']				=	site_url("admin");
						$seo['title']			=	WEBSITENAME;
						$seo['metatitle']		=	WEBSITENAME;
						$seo['metadescription']	=	WEBSITENAME;
								$data['data']['seo']	=	$seo;

							$currentime	=	getgmttime();
									// date(newsdate) < '$currentime' AND
									$sortnew	=	"
														(
																news.status = '1'
														)
													";
						//	$currentime = 1;

							$trendingsql	=	"
													SELECT 
														news.title, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													LEFT JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'normal'
													AND
														 $sortnew
													GROUP BY news.Id 
													ORDER BY news.trending ASC
													LIMIT 8
												";

										$trendingdata = $this->db->query($trendingsql);		
											$data['trendingdata'] = $trendingdata->result_array();

									$data['layout'] = $this->webLayout($data);
	                    $this->load->view('front/index.php',$data);
				}
				
			public function dubaitime()
				{	
					date_default_timezone_set('UTC');
					echo "<h1>GMT is: " . date('Y/m/d H:i:s')."</h1>";					
					
					date_default_timezone_set('Asia/Dubai');
					echo "<h1>Mr. Reyaz ji time is: " . date('Y/m/d H:i:s')."</h1>";
					
					date_default_timezone_set('Asia/kolkata');
					echo "<h1>Our time is: " . date('Y/m/d H:i:s')."</h1>";
					// echo "error404";
				}
				
				
			public function cricketbanner()
				{
					$data = array();
					$seo = array();
				
													
										
					$data['layout'] = $this->webLayout($data);
					$this->load->view('front/cricketbanner/index.php',$data);
	    		}
				
				
			public function carads()
				{
					$data = array();
					$seo = array();

					$data['layout'] = $this->webLayout($data);
					$this->load->view('front/carads/index.php',$data);
	    		}
		
		}

?>