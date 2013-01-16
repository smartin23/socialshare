<?php

defined( '_JEXEC' ) or die();

jimport( 'joomla.event.plugin' );


class plgContentsocialshare extends JPlugin 
{

	function plgContentsocialshare( &$subject, $params ) 
	{
		parent::__construct( $subject, $params );
 	}

	function onContentPrepare( $context, &$row, &$params, $limitstart=0 )
	{
		global $mainframe;

		static $first_og=1;

	 	$regex = '/{(socialshare)\s*(.*?)}/i';
	
		$plugin	=& JPluginHelper::getPlugin('content', 'socialshare');
		$pluginParams = $this->params;
	
		$send_button=$pluginParams->get('send_button','');
		if ($send_button=="0") $send_button="false";
		if ($send_button=="1") $send_button="true";
	
		$layout=$pluginParams->get('layout','');
	
		$show_faces=$pluginParams->get('show_faces','');
		if ($show_faces=="0") $show_faces="false";
		if ($show_faces=="1") $show_faces="true";
	
		$width=$pluginParams->get('width','');
		$action=$pluginParams->get('action','');
		$colorscheme=$pluginParams->get('colorscheme','');
		$app_id=$pluginParams->get('app_id','');
		$og_url=$pluginParams->get('og_url','');
		$og_type=$pluginParams->get('og_type','article');
		$og_image=$pluginParams->get('og_image','');
		$url_from=$pluginParams->get('url_from','');
		$url_to=$pluginParams->get('url_to','');
	
		$uri =& JURI::getInstance();
		$curl = $uri->toString();
	
		$config =& JFactory::getConfig();
	
		$lang=&JFactory::getLanguage();
		$lang_tag=$lang->getTag();
		$lang_tag=str_replace("-","_",$lang_tag);
	
		$matches = array();
		preg_match_all( $regex, $row->text, $matches, PREG_SET_ORDER );
	
		$doc =& JFactory::getDocument();
		if ($first_og && (count($matches)>0))
		{
			if ($app_id!="") {$doc->addCustomTag('<meta property="fb:app_id" content="'.$app_id.'"/>');}
			if ($og_url=="1")
			{
				$doc->addCustomTag('<meta property="og:type" content="'.$og_type.'"/>');
				$doc->addCustomTag('<meta property="og:url" content="'.$curl.'"/>');
				$doc->addCustomTag('<meta property="og:site_name" content="'.$config->getValue('config.sitename').'"/>');
				$doc->addCustomTag('<meta property="og:locale" content="'.$lang_tag.'"/>');
			}
			if ($og_image!="") $doc->addCustomTag('<meta property="og:image" content="'.$og_image.'"/>');
		}
		$first_og=0;
	
		foreach ($matches as $args) 
		{
			$args=str_replace(" ","&", $args);
			parse_str( $args[2], $pars );
	
			$str="";
	
			if (isset($pars['lang'])) {$lang_tag=$pars['lang'];}
	
			$uri =& JURI::getInstance();
			$curl = $uri->toString();
	
			$curl = str_replace("https://","http://",$curl);

			$id="";if (isset($pars['id'])) {$id=$pars['id'];}
			if ($id!="")	
			{
				$article = JTable::getInstance('content');
				$article->load($id);
				$slug = $article->get('id').':'.$article->get('alias');
				$catid = $article->get('catid');
				$catslug = $catid ? $catid .':'.$article->get('category_alias') : $catid;
				$sectionid = $article->get('sectionid');

				$curl = 'http://';
				if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"]==”on”) {$curl='https://';};
				$curl .= $_SERVER["SERVER_NAME"];
				$curl .= JRoute::_(ContentHelperRoute::getArticleRoute($slug, $catslug, $sectionid));
			}

			if ($url_from!="") $curl = str_replace($url_from,$url_to,$curl);

			$url="<span id=\"socialshare-facebook-button\">
					<a title=\"Casa Nova Maisons en bois\" onClick=\"window.open('http://www.facebook.com/sharer.php?s=100&amp;p[title]=CasaNova - Maisons en bois&amp;p[url]=http://casanova.lagrangeweb.fr', 'sharer', 'toolbar=0,status=0,width=548,height=325');\" href=\"javascript: void(0)\"> 
</a>
</span>
<span id=\"custom-facebook-count\" class=\"valeur\">".get_fb_count('http://casanova.lagrangeweb.fr')."
</span>";
			
			$row->text = preg_replace($regex, $url, $row->text, 1);
		}
	}
	
	function get_fb_count($url) {

			
		$json_string = file_get_contents('http://graph.facebook.com/?ids=' . $url);
		$json = json_decode($json_string, true);
		return intval( $json[$url]['shares'] );
	}
}
?>