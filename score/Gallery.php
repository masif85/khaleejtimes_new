<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gallery extends My_Controller 
		{
	
		public function __construct()
        {
		  
          parent::__construct();
            date_default_timezone_set('America/Los_Angeles');
			$this->load->model('InformationModel');
			$this->load->library('pagination');								
									$session = array("isshow"=>0);
										$this->session->set_userdata($session);
        }
		
			public function latest($page)
				{
					// echo "aaaaaaaaa";
					$orderby	=	"news.newsdate DESC";
					$data = array();
					$seo = array();
						$seo['url']				=	site_url("admin");
						$seo['title']			=	WEBSITENAME;
						$seo['metatitle']		=	WEBSITENAME;
						$seo['metadescription']	=	WEBSITENAME;
						$data['data']['seo']	=	$seo;
						$data['pagename']		=	"Latest IPL NEWS";
						$currentime	=	getgmttime();
						$config['base_url'] = base_url("latest/");
									$sortnew	=	"
														(
																date(newsdate) <= '$currentime' 
															AND
																news.status = '1'
														)
													";
						
						// $config['total_rows'] = $this->InformationModel->countallrowwhereclouse("news","gallery",$sortnew);
						$config['per_page'] = 42;
						$counter = $config['per_page'];
						
							if(empty($page))
									{
										$page = 1;
									}
									$page--;	if($page<0) $page=0;
								$limitcount = $page*$counter;
									

											$sql = "
														SELECT 
															news.title,news.hits, news.newsdate, news.description, news.Id , news.alias, news.updated ,news.metadescription, news_images.url, news_images.source,  categroy.cat_title 
														FROM 
															news 
														LEFT JOIN news_cat on news_cat.news_id = news.Id 
														LEFT JOIN categroy on categroy.cat_id = news_cat.cat_id 
														
														LEFT JOIN news_images on news_images.news_id = news.Id 
														WHERE
															$sortnew
														GROUP BY news.Id 
														ORDER BY $orderby
														LIMIT $limitcount,$counter 
													";
											$sql = $this->db->query($sql);		
											$data['gallery'] = $sql->result_array();
																										
										if(empty($data['gallery']))
											redirect(site_url());
											$sql = "
														SELECT 
															count(distinct news.Id) as total
														FROM 
															news 
														LEFT JOIN news_cat on news_cat.news_id = news.Id 
														LEFT JOIN categroy on categroy.cat_id = news_cat.cat_id 
														LEFT JOIN admin on admin.userid = news.writerid 
														LEFT JOIN news_images on news_images.news_id = news.Id 
														WHERE
															$sortnew
													";
											$temp = $this->db->query($sql);		
											$temp = $temp->result_array();
											
											
								$config['total_rows'] = isset($temp[0]['total'])?$temp[0]['total']:0;		
											
											$config['use_page_numbers'] = TRUE;
											$config['full_tag_open'] 	= 	"<ul class='pagination'>";
											$config['full_tag_close'] 	= 	'</ul>';
											$config['num_tag_open'] 	= 	'<li>';
											$config['num_tag_close'] 	= 	'</li>';
											$config['cur_tag_open'] 	= 	'<li class="active"><a>';
											$config['cur_tag_close'] 	= 	'</a></li>';
											$config['prev_tag_open'] 	= 	'<li>';
											$config['prev_tag_close'] 	= 	'</li>';
											$config['first_tag_open'] 	= 	'<li>';
											$config['first_tag_close'] 	= 	'</li>';
											$config['last_tag_open'] 	= 	'<li>';
											$config['last_tag_close'] 	= 	'</li>';
											$config['prev_link'] 		= 	'<i class="fa fa-long-arrow-left"></i>';
											$config['prev_tag_open'] 	= 	'<li>';
											$config['prev_tag_close'] 	= 	'</li>';
											$config['next_link'] 		= 	'<i class="fa fa-long-arrow-right"></i>';
											$config['next_tag_open'] 	=	'<li>';
											$config['next_tag_close'] 	=	'</li>';
											$this->pagination->initialize($config);
							$data['pagination']		=	$this->pagination->create_links(); 
							
								$fortrending = date("Y-m-d",time()-(7*60*60*24));
							
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
													AND
														date(news.newsdate) > '$fortrending' 
													GROUP BY news.Id 
													ORDER BY news.hits DESC 
													LIMIT 15
												";
										$trendingdata = $this->db->query($latestsql);		
											$data['trendingdata'] = $trendingdata->result_array();
							
							
							
							$data['layout'] = $this->webLayout($data);
							$this->load->view('front/allnews.php',$data);
	        }
			
			
			public function category($slug,$catid,$page) 
				{
					$data = array();
					$seo = array();
						$seo['url']				=	base_url("category/$slug/$catid");
						$currentime	=	getgmttime();
						$config['base_url'] = base_url("category/$slug/$catid");
									$sortnew	=	"
														(
																date(newsdate) <= '$currentime' 
															AND
																news.status = '1'
														)
													";
						
						// $config['total_rows'] = $this->InformationModel->countallrowwhereclouse("news","gallery",$sortnew);
						$config['per_page'] = 18; 
						$counter = $config['per_page'];
						
							if(empty($page))
									{
										$page = 1;
									}
									$page--;	if($page<0) $page=0;
								$limitcount = $page*$counter;
									
			$catsort	=	"categroy.cat_id = '$catid'";						

	if(isset($catid))
		{										
			switch($catid)
				{
					case 10: // CSK
						$catsort	=	"	
											(
													categroy.cat_id = '$catid'
												OR
													lower(news.description) LIKE '%csk%'
												OR
													lower(news.description) LIKE '%ms dhoni%'
												OR
													lower(news.description) LIKE '%super kings%'
												OR
													lower(news.description) LIKE '%dhoni%'
											)
										";
					break;
					
					case 11: // RCB
						$catsort	=	"	
											(
													categroy.cat_id = '$catid'
												OR
													lower(news.description) LIKE '%rcb%'
												OR
													lower(news.description) LIKE '%royal challengers%'
												OR
													lower(news.description) LIKE '%challengers%'
												OR
													lower(news.description) LIKE '%kohli%'
											)
										";
					break;
					
					case 12: // KKR
						
						$catsort	=	"	
											(
													categroy.cat_id = '$catid'
												OR
													lower(news.description) LIKE '%kkr%'
												OR
													lower(news.description) LIKE '%knight riders%'
												OR
													lower(news.description) LIKE '%knight%'
											)
										";
					break;
					
					case 13: // SH
						$catsort	=	"	
											(
													categroy.cat_id = '$catid'
												OR
													lower(news.description) LIKE '%srh%'
												OR
													lower(news.description) LIKE '%sunrisers hyderabad%'
												OR
													lower(news.description) LIKE '%sunrisers%'
											)
										";
					break;
					
					case 14: // MI
						$catsort	=	"	
											(
													categroy.cat_id = '$catid'
												OR
													lower(news.description) LIKE '%mumbai indians%'
												OR
													lower(news.description) LIKE '%indians%'
												OR
													lower(news.description) LIKE '%rohit sharma%'
											)
										";
					break;
					
					case 15: // DD
						$catsort	=	"	
											(
													categroy.cat_id = '$catid'
												OR
													lower(news.description) LIKE '%delhi capitals%'
												OR
													lower(news.description) LIKE '%DD%'
												OR
													lower(news.description) LIKE '%capitals%'
											)
										";
					break;
					
					case 16: // RR 
						
						$catsort	=	"	
											(
													categroy.cat_id = '$catid'
												OR
													lower(news.description) LIKE '%rajasthan royals%'
												OR
													lower(news.description) LIKE '%RR%'
												OR
													lower(news.description) LIKE '%royals%'
											)
										";
					break;
					
					case 17: //KXIP	
						$catsort	=	"	
											(
													categroy.cat_id = '$catid'
												OR
													lower(news.description) LIKE '%kings xi punjab%'
												OR
													lower(news.description) LIKE '%kings xi%'
												OR
													lower(news.description) LIKE '%kings%'
												OR
													lower(news.description) LIKE '%kl rahul%'
											)
										";
					break;
					
					
					default:
					break;
					
				}
		}

											$sql = "
														SELECT 
															news.title,news.hits, news.newsdate, news.Id , news.alias, news.updated ,news.metadescription, news_images.url, news_images.source, admin.username, categroy.cat_title 
														FROM 
															news 
														LEFT JOIN news_cat on news_cat.news_id = news.Id 
														LEFT JOIN categroy on categroy.cat_id = news_cat.cat_id 
														LEFT JOIN admin on admin.userid = news.writerid 
														LEFT JOIN news_images on news_images.news_id = news.Id 
														WHERE
															$sortnew
														AND
															$catsort
														GROUP BY news.Id 
														ORDER BY news.newsdate DESC 
														LIMIT $limitcount,$counter 
													";
													
														$sql = $this->db->query($sql);

															$data['gallery'] = $sql->result_array();
															
							
					$orderby	=	"news.newsdate DESC";			 		 
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
													LIMIT 21
												";
										$latestdata = $this->db->query($latestsql);		
											$data['latestdata'] = $latestdata->result_array();

											
										if(empty($data['gallery']))
											redirect(site_url());	
															 
								$data['slug'] 	=	$slug;
								$data['iscate'] 	=	1;
								$data['catname'] 	=	$data['gallery'][0]['cat_title'];
								
						
	if(isset($catid))
		{										
			switch($catid)
				{
					case 10: // CSK
						$data['fullcatname'] 	=	"Chennai Super Kings";
						$data['catname'] 	=	"CSK";
					break;
					
					case 11: // RCB
						$data['fullcatname'] 	=	"Royal Challengers Bangalore";
						$data['catname'] 	=	"RCB";
					break;
					
					case 12: // KKR
						$data['fullcatname'] 	=	"Kolkata Knight Riders";
						$data['catname'] 	=	"KKR";
					break;
					
					case 13: // SH
						$data['fullcatname'] 	=	"Sunrisers Hyderabad";
						$data['catname'] 	=	"SRH";
					break;
					
					case 14: // MI
						$data['fullcatname'] 	=	"Mumbai Indians";
						$data['catname'] 	=	"MI";
					break;
					
					case 15: // DD
						$data['fullcatname'] 	=	"Delhi Capitals";
						$data['catname'] 	=	"DC";
					break;
					
					case 16: // RR
						$data['fullcatname'] 	=	"Rajasthan Royals";
						$data['catname'] 	=	"RR";
					break;
					
					case 17: //KXIP	
						$data['fullcatname'] 	=	"Punjab Kings";
						$data['catname'] 	=	"PBKS";
					break;
					
					
					default:
					break;
					
				}
		}		
								
								
								$data['catid'] 	=	$catid;
															
											
											$sql = "
														SELECT 
															count(distinct news.Id) as total
														FROM 
															news 
														LEFT JOIN news_cat on news_cat.news_id = news.Id 
														LEFT JOIN categroy on categroy.cat_id = news_cat.cat_id 
														LEFT JOIN admin on admin.userid = news.writerid 
														LEFT JOIN news_images on news_images.news_id = news.Id 
														WHERE
															$sortnew
														AND
															categroy.cat_id = '$catid'
													";
											$temp = $this->db->query($sql);		
											$temp = $temp->result_array();

								$config['total_rows'] = isset($temp[0]['total'])?$temp[0]['total']:0;		
								
										if($config['total_rows']<1)
											redirect(site_url());
								
											
											$config['use_page_numbers'] = TRUE;
											$config['full_tag_open'] 	= 	"<ul class='pagination'>";
											$config['full_tag_close'] 	= 	'</ul>';
											$config['num_tag_open'] 	= 	'<li>';
											$config['num_tag_close'] 	= 	'</li>';
											$config['cur_tag_open'] 	= 	'<li class="active"><a>';
											$config['cur_tag_close'] 	= 	'</a></li>';
											$config['prev_tag_open'] 	= 	'<li>';
											$config['prev_tag_close'] 	= 	'</li>';
											$config['first_tag_open'] 	= 	'<li>';
											$config['first_tag_close'] 	= 	'</li>';
											$config['last_tag_open'] 	= 	'<li>';
											$config['last_tag_close'] 	= 	'</li>';
											$config['prev_link'] 		= 	'<i class="fa fa-long-arrow-left"></i>';
											$config['prev_tag_open'] 	= 	'<li>';
											$config['prev_tag_close'] 	= 	'</li>';
											$config['next_link'] 		= 	'<i class="fa fa-long-arrow-right"></i>';
											$config['next_tag_open'] 	=	'<li>';
											$config['next_tag_close'] 	=	'</li>';
											$this->pagination->initialize($config);
							$data['pagination']		=	$this->pagination->create_links();
							$data['layout'] = $this->webLayout($data);
							$this->load->view('front/allnews.php',$data);
	        }

			public function index($page)
				{
					$data = array();
					$seo = array();
						$seo['url']				=	site_url("admin");
						$seo['title']			=	WEBSITENAME;
						$seo['metatitle']		=	WEBSITENAME;
						$seo['metadescription']	=	WEBSITENAME;
						$data['data']['seo']	=	$seo;
						$currentime	=	getgmttime();
						$config['base_url'] = base_url("gallery/");
						$sortnew	=	"
														(
																news.status = '1'
														)
													";
									/* $sortnew	=	"
														(
																date(newsdate) <= '$currentime' 
															AND
																news.status = '1'
														)
													"; */
						$config['total_rows'] = $this->InformationModel->countallrowwhereclouse("news","gallery",$sortnew);
										if($config['total_rows']<1)
											redirect(site_url());
						$config['per_page'] = 18;
						$counter = $config['per_page'];
						
							if(empty($page))
									{
										$page = 1;
									}
									$page--;	if($page<0) $page=0;
								$limitcount = $page*$counter;
									

											$sql = "
														SELECT 
															news.title,news.hits, news.newsdate, news.description, news.Id , news.alias, news.updated ,news.metadescription, news_images.url, news_images.source, admin.username, categroy.cat_title 
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
														ORDER BY news.newsdate DESC 
														LIMIT $limitcount,$counter 
													";
													
											$sql2 = "
														SELECT 
														news.title,news.hits, news.newsdate, news.metadescription, news.Id , news.alias, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													INNER JOIN categroy on categroy.cat_id = news_cat.cat_id 
													LEFT JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'gallery'
													AND
														$sortnew
													GROUP BY news.Id 
													ORDER BY news.newsdate DESC 
													LIMIT $limitcount,$counter 
													";
											$sql = $this->db->query($sql);		
											$data['gallery'] = $sql->result_array();
																										
										if(empty($data['gallery']))
											redirect(site_url());

											$sql = "
														SELECT 
															count(distinct news.Id) as total
														FROM 
															news 
														INNER JOIN news_cat on news_cat.news_id = news.Id 
														LEFT JOIN categroy on categroy.cat_id = news_cat.cat_id 
														LEFT JOIN admin on admin.userid = news.writerid 
														LEFT JOIN news_images on news_images.news_id = news.Id 
														WHERE
															`news`.`newstype` = 'gallery'
														AND	
															$sortnew
													";
											$temp = $this->db->query($sql);		
											$temp = $temp->result_array();
											
											
								$config['total_rows'] = isset($temp[0]['total'])?$temp[0]['total']:0;	
										if($config['total_rows']<1)
											redirect(site_url());
											
											$config['use_page_numbers'] = TRUE;
											$config['full_tag_open'] 	= 	"<ul class='pagination'>";
											$config['full_tag_close'] 	= 	'</ul>';
											$config['num_tag_open'] 	= 	'<li>';
											$config['num_tag_close'] 	= 	'</li>';
											$config['cur_tag_open'] 	= 	'<li class="active"><a>';
											$config['cur_tag_close'] 	= 	'</a></li>';
											$config['prev_tag_open'] 	= 	'<li>';
											$config['prev_tag_close'] 	= 	'</li>';
											$config['first_tag_open'] 	= 	'<li>';
											$config['first_tag_close'] 	= 	'</li>';
											$config['last_tag_open'] 	= 	'<li>';
											$config['last_tag_close'] 	= 	'</li>';
											$config['prev_link'] 		= 	'<i class="fa fa-long-arrow-left"></i>';
											$config['prev_tag_open'] 	= 	'<li>';
											$config['prev_tag_close'] 	= 	'</li>';
											$config['next_link'] 		= 	'<i class="fa fa-long-arrow-right"></i>';
											$config['next_tag_open'] 	=	'<li>';
											$config['next_tag_close'] 	=	'</li>';
											$this->pagination->initialize($config);
							$data['pagination']		=	$this->pagination->create_links(); 
							$data['layout'] = $this->webLayout($data);
							$this->load->view('front/photogallery.php',$data);
	        }
				
			public function video($page)
				{
					$data = array();
					$seo = array();
						$seo['url']				=	site_url("admin");
						$seo['title']			=	WEBSITENAME;
						$seo['metatitle']		=	WEBSITENAME;
						$seo['metadescription']	=	WEBSITENAME;
						$data['data']['seo']	=	$seo;
						$config['base_url'] = base_url("video/");
						$currentime	=	getgmttime();
						$sortnew	=	"
														(
																news.status = '1'
														)
													";
									$sortnew	=	"
														(
																date(newsdate) <= '$currentime' 
															AND
																news.status = '1'
														)
													";
						$config['total_rows'] = $this->InformationModel->countallrowwhereclouse("news","video",$sortnew);
										if($config['total_rows']<1)
											redirect(site_url());
						$config['per_page'] = 24;
						$counter = $config['per_page'];
						
							if(empty($page))
									{
										$page = 1;
									}
									$page--;	if($page<0) $page=0;
								$limitcount = $page*$counter;
									

						$sql	=	"
													SELECT 
														news.title,news.hits, news.newsdate, news.metadescription, news.Id , news.alias, news.video, news.description, news.updated , news_images.url, news_images.source, admin.username, categroy.cat_title 
													FROM 
														news 
													INNER JOIN news_cat on news_cat.news_id = news.Id 
													LEFT JOIN categroy on categroy.cat_id = news_cat.cat_id 
													LEFT JOIN admin on admin.userid = news.writerid 
													LEFT JOIN news_images on news_images.news_id = news.Id 
													WHERE
														`news`.`newstype` = 'video'
													AND	
														$sortnew
													GROUP BY news.Id 
													ORDER BY news.newsdate DESC 
														LIMIT $limitcount,$counter 
													";
											$sql = $this->db->query($sql);		
											$data['video'] = $sql->result_array();
																										
										if(empty($data['video']))
											redirect(site_url());
											
											
											$sql = "
														SELECT 
															count(distinct news.Id) as total
														FROM 
															news 
														INNER JOIN news_cat on news_cat.news_id = news.Id 
														LEFT JOIN categroy on categroy.cat_id = news_cat.cat_id 
														LEFT JOIN admin on admin.userid = news.writerid 
														LEFT JOIN news_images on news_images.news_id = news.Id 
														WHERE
															`news`.`newstype` = 'video'
														AND	
															$sortnew
													";
											$temp = $this->db->query($sql);		
											$temp = $temp->result_array();
											
											
								$config['total_rows'] = isset($temp[0]['total'])?$temp[0]['total']:0;
											
											$config['use_page_numbers'] = TRUE;
											$config['full_tag_open'] 	= 	"<ul class='pagination'>";
											$config['full_tag_close'] 	= 	'</ul>';
											$config['num_tag_open'] 	= 	'<li>';
											$config['num_tag_close'] 	= 	'</li>';
											$config['cur_tag_open'] 	= 	'<li class="active"><a>';
											$config['cur_tag_close'] 	= 	'</a></li>';
											$config['prev_tag_open'] 	= 	'<li>';
											$config['prev_tag_close'] 	= 	'</li>';
											$config['first_tag_open'] 	= 	'<li>';
											$config['first_tag_close'] 	= 	'</li>';
											$config['last_tag_open'] 	= 	'<li>';
											$config['last_tag_close'] 	= 	'</li>';
											$config['prev_link'] 		= 	'<i class="fa fa-long-arrow-left"></i>';
											$config['prev_tag_open'] 	= 	'<li>';
											$config['prev_tag_close'] 	= 	'</li>';
											$config['next_link'] 		= 	'<i class="fa fa-long-arrow-right"></i>';
											$config['next_tag_open'] 	=	'<li>';
											$config['next_tag_close'] 	=	'</li>';
											$this->pagination->initialize($config);
											$data['pagination']		=	$this->pagination->create_links();  
										$data['hideexitwidget']	=	1;
										
							$data['layout'] = $this->webLayout($data);
							$this->load->view('front/video.php',$data);
				}
				
		}

?>