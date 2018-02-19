<?php
/**
 * @package    DD_Article_Slides
 *
 * @author     HR IT-Solutions Florian Häusler <info@hr-it-solutions.com>
 * @copyright  Copyright (C) 2017 - 2017 Didldu e.K. | HR IT-Solutions
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
**/

defined('_JEXEC') or die;

/**
 * DD_Tabs Plugin
 *
 * @since  1.0.0.0
 */
class PlgSystemDD_Article_Slides extends JPlugin
{
	protected $app;

	protected $autoloadLanguage = true;

	/**
	 * Adds additional fields to the content editing form
	 *
	 * @param   JForm $form The form to be altered.
	 * @param   mixed $data The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since Version 1.1.0.0
	 */
    public function onContentPrepareForm($form, $data)
    {
		$name = $form->getName();

	    if ($name != 'com_content.article')
        {
			return;
		}

		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		JForm::addFormPath(dirname(__FILE__) . '/fields');

		// Add dd_tabs form to the joomla article tabs
		$form->loadFile('mulitform_slides', false);

		return true;
    }

	/**
	 * Plugin to add article slides options to the article.edit context.
	 *
	 * @param   string   $context   The context of the content being passed to the plugin.
	 * @param   object   &$article  The article object.  Note $article->text is also available
	 * @param   mixed    &$params   The article params
	 * @param   integer  $page      The 'page' number
	 *
	 * @return  mixed   true if there is an error. Void otherwise.
	 *
	 * @since   Version  1.0.0.0
	 */
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		// Don't run this plugin when the content is being indexed
		if ($context === 'com_finder.indexer')
		{
			return true;
		}

		// Expression to search for (dd_article_slides)
		$regex = '/{dd_article_slides}{\/dd}/s';

		// Find all instances
		preg_match($regex, $article->text, $matche);

		// Slider in html
		if($matche)
		{
			JHtml::_('jquery.framework');

			JHtml::_('script', 'plg_system_dd_article_slides/article_slides.js', array('version' => 'auto', 'relative' => true));

			JHtml::_('stylesheet', 'plg_system_dd_article_slides/article_slides.css', array('version' => 'auto', 'relative' => true));

			JHtml::_('stylesheet', 'plg_system_dd_article_slides/style.css', array('version' => 'auto', 'relative' => true));

			$article->text = str_replace(
				$matche[0],
				$this->getSliderHTML($article->id),
				$article->text
			);
		}
	}

	/**
	 * getSliderHTML
	 *
	 * @param  integer  $article_id   The article id
	 *
	 * @return  mixed   the slider html
	 *
	 * @since   Version  1.0.0.0
	 */
	private function getSliderHTML($article_id)
	{

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('attribs')
			->from($db->quoteName('#__content'))
			->where($db->quoteName('id') . '=' . $article_id);
		$db->setQuery($query);
		$articleAttribs = (json_decode($db->loadResult()));


		$html = '<div id="dd_article_slides">';

		$i = 0;

		foreach ($articleAttribs->dd_article_slides_fields as $slide)
		{
			$i++;
			$html .= '<div id="dd_article_slides_fields' . $i . '" class="article_slide">';
			$html .=    '<div class="article_slide_inner">';
			$html .=        '<img src="' . $slide->image . '" alt="'. $slide->image_alt .'">';
			$html .=        '<div class="article_slide_info">';
			$html .=            '<p>' . $slide->image_desc .'</p>';
			$html .=            '<small>Bild: '. $slide->image_source . '</small>';
			$html .=        '</div>';
			$html .=        '<button class="article_slide_info_toggle">';
			$html .=            '<span class="icon-dd-arrow-up"></span>';
			$html .=            '<span class="icon-dd-arrow-down"></span>';
			$html .=        '</button>';
			$html .=    '</div>';
			$html .= '</div>';
		}

			$html .= '<div class="article_slides_controls">';
			$html .=     '<span class="icon-dd-arrow-left" id="slide-left"></span>';
			$html .=     '<span id="article_slide_active">1</span> / <span>'. count( (array) $articleAttribs->dd_article_slides_fields) .'</span>';
			$html .=     '<span class="icon-dd-arrow-right" id="slide-right"></span>';
			$html .= '</div>';

		$html .= '</div>';

		return $html;
	}
}
