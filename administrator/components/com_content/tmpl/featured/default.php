<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Button\FeaturedButton;
use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Content\Administrator\Helper\ContentHelper;

HTMLHelper::_('behavior.multiselect');

$user      = Factory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'fp.ordering';

if (strpos($listOrder, 'publish_up') !== false)
{
	$orderingColumn = 'publish_up';
}
elseif (strpos($listOrder, 'publish_down') !== false)
{
	$orderingColumn = 'publish_down';
}
else
{
	$orderingColumn = 'created';
}


if ($saveOrder && !empty($this->items))
{
	$saveOrderingUrl = 'index.php?option=com_content&task=featured.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}

$js = <<<JS
(function() {
	document.addEventListener('DOMContentLoaded', function() {
	  var elements = [].slice.call(document.querySelectorAll('.article-status'));

	  elements.forEach(function (element) {
		element.addEventListener('click', function(event) {
			event.stopPropagation();
		});
	  });
	});
})();
JS;

// @todo mode the script to a file
$this->document->addScriptDeclaration($js);

HTMLHelper::_('script', 'com_content/admin-articles-workflow-buttons.js', ['relative' => true, 'version' => 'auto']);

?>

<form action="<?php echo Route::_('index.php?option=com_content&view=featured'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php
				// Search tools bar
				echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
				?>
				<?php if (empty($this->items)) : ?>
					<div class="alert alert-info">
						<span class="fa fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>
					<table class="table" id="articleList">
						<caption id="captionTable" class="sr-only">
							<?php echo Text::_('COM_CONTENT_FEATURED_TABLE_CAPTION'); ?>, <?php echo Text::_('JGLOBAL_SORTED_BY'); ?>
						</caption>
						<thead>
							<tr>
								<td style="width:1%" class="text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</td>
								<th scope="col" style="width:1%" class="text-center d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', '', 'fp.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
								</th>
								<th scope="col" style="width:1%" class="text-center">
									<?php echo Text::_('JFEATURED'); ?>
								</th>
								<th scope="col" style="width:1%; min-width:85px" class="text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
								</th>
								<th scope="col">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:10%" class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:10%" class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JAUTHOR', 'a.created_by', $listDirn, $listOrder); ?>
								</th>
								<?php if (Multilanguage::isEnabled()) : ?>
									<th scope="col" style="width:10%" class="d-none d-md-table-cell">
										<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
									</th>
								<?php endif; ?>
								<th scope="col" style="width:10%" class="d-none d-md-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_CONTENT_HEADING_DATE_' . strtoupper($orderingColumn), 'a.' . $orderingColumn, $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:3%" class="d-none d-lg-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
								</th>
								<?php if ($this->vote) : ?>
									<th scope="col" style="width:3%" class="d-none d-md-table-cell text-center">
										<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_VOTES', 'rating_count', $listDirn, $listOrder); ?>
									</th>
									<th scope="col" style="width:3%" class="d-none d-md-table-cell text-center">
										<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_RATINGS', 'rating', $listDirn, $listOrder); ?>
									</th>
								<?php endif; ?>
								<th scope="col" style="width:3%" class="d-none d-lg-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody<?php if ($saveOrder) : ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>"<?php endif; ?>>
						<?php $count = count($this->items); ?>
						<?php foreach ($this->items as $i => $item) :
							$item->max_ordering = 0;
							$ordering         = ($listOrder == 'fp.ordering');
							$assetId          = 'com_content.article.' . $item->id;
							$canCreate        = $user->authorise('core.create', 'com_content.category.' . $item->catid);
							$canEdit          = $user->authorise('core.edit', 'com_content.article.' . $item->id);
							$canCheckin       = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
							$canChange        = $user->authorise('core.edit.state', 'com_content.article.' . $item->id) && $canCheckin;
							$canEditCat       = $user->authorise('core.edit',       'com_content.category.' . $item->catid);
							$canEditOwnCat    = $user->authorise('core.edit.own',   'com_content.category.' . $item->catid) && $item->category_uid == $userId;
							$canEditParCat    = $user->authorise('core.edit',       'com_content.category.' . $item->parent_category_id);
							$canEditOwnParCat = $user->authorise('core.edit.own',   'com_content.category.' . $item->parent_category_id) && $item->parent_category_uid == $userId;

							$transitions = ContentHelper::filterTransitions($this->transitions, $item->stage_id, $item->workflow_id);

							$publish = 0;
							$unpublish = 0;
							$archive = 0;
							$trash = 0;

							foreach ($transitions as $transition) :
								switch ($transition['stage_condition']) :
									case ContentComponent::CONDITION_PUBLISHED:
										++$publish;
										break;
									case ContentComponent::CONDITION_UNPUBLISHED:
										++$unpublish;
										break;
									case ContentComponent::CONDITION_ARCHIVED:
										++$archive;
										break;
									case ContentComponent::CONDITION_TRASHED:
										++$trash;
										break;
								endswitch;
							endforeach;

							?>
							<tr class="row<?php echo $i % 2; ?>" data-dragable-group="<?php echo $item->catid; ?>"
								data-condition-publish="<?php echo (int) ($publish > 0); ?>"
								data-condition-unpublish="<?php echo (int) ($unpublish > 0); ?>"
								data-condition-archive="<?php echo (int) ($archive > 0); ?>"
								data-condition-trash="<?php echo (int) ($trash > 0); ?>"
								data-workflow_id="<?php echo (int) $item->workflow_id; ?>"
								data-stage_id="<?php echo (int) $item->stage_id; ?>"
							>
								<td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td class="text-center d-none d-md-table-cell">
									<?php
									$iconClass = '';

									if (!$canChange)
									{
										$iconClass = ' inactive';
									}
									elseif (!$saveOrder)
									{
										$iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');
									}
									?>
									<span class="sortable-handler<?php echo $iconClass ?>">
										<span class="fa fa-ellipsis-v" aria-hidden="true"></span>
									</span>
									<?php if ($canChange && $saveOrder) : ?>
										<input type="text" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order hidden">
									<?php endif; ?>
								</td>
								<td class="text-center">
									<?php

										$options = [
											'disabled' => !$canChange
										];

										echo (new FeaturedButton)
											->render($item->featured, $i, $options, $item->featured_up, $item->featured_down);
									?>
								</td>
								<td class="article-status">
									<div class="d-flex">
										<div class="btn-group tbody-icon mr-1">
										<?php

											$options = [
												'transitions' => $transitions,
												'stage' => Text::_($item->stage_title),
												'id' => (int) $item->id
											];

											echo (new PublishedButton)
													->removeState(0)
													->removeState(1)
													->removeState(2)
													->removeState(-2)
													->addState(ContentComponent::CONDITION_PUBLISHED, '', 'publish', Text::_('COM_CONTENT_CHANGE_STAGE'), ['tip_title' => Text::_('JPUBLISHED')])
													->addState(ContentComponent::CONDITION_UNPUBLISHED, '', 'unpublish', Text::_('COM_CONTENT_CHANGE_STAGE'), ['tip_title' => Text::_('JUNPUBLISHED')])
													->addState(ContentComponent::CONDITION_ARCHIVED, '', 'archive', Text::_('COM_CONTENT_CHANGE_STAGE'), ['tip_title' => Text::_('JARCHIVED')])
													->addState(ContentComponent::CONDITION_TRASHED, '', 'trash', Text::_('COM_CONTENT_CHANGE_STAGE'), ['tip_title' => Text::_('JTRASHED')])
													->setLayout('joomla.button.transition-button')
													->render($item->stage_condition, $i, $options, $item->publish_up, $item->publish_down);
										?>
										</div>
									</div>
								</td>
								<th scope="row" class="has-context">
									<div class="break-word">
										<?php if ($item->checked_out) : ?>
											<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'articles.', $canCheckin); ?>
										<?php endif; ?>
										<?php if ($canEdit) : ?>
											<a href="<?php echo Route::_('index.php?option=com_content&task=article.edit&return=featured&id=' . $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($item->title)); ?>">
												<?php echo $this->escape($item->title); ?></a>
										<?php else : ?>
											<span title="<?php echo Text::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->title); ?></span>
										<?php endif; ?>
										<span class="small break-word">
											<?php if (empty($item->note)) : ?>
												<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
											<?php else : ?>
												<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
											<?php endif; ?>
										</span>
										<div class="small">
											<?php
											$ParentCatUrl = Route::_('index.php?option=com_categories&task=category.edit&id=' . $item->parent_category_id . '&extension=com_content');
											$CurrentCatUrl = Route::_('index.php?option=com_categories&task=category.edit&id=' . $item->catid . '&extension=com_content');
											$EditCatTxt = Text::_('COM_CONTENT_EDIT_CATEGORY');
											echo Text::_('JCATEGORY') . ': ';
											if ($item->category_level != '1') :
												if ($item->parent_category_level != '1') :
													echo ' &#187; ';
												endif;
											endif;
											if (Factory::getLanguage()->isRtl())
											{
												if ($canEditCat || $canEditOwnCat) :
													echo '<a href="' . $CurrentCatUrl . '" title="' . $EditCatTxt . '">';
												endif;
												echo $this->escape($item->category_title);
												if ($canEditCat || $canEditOwnCat) :
													echo '</a>';
												endif;
												if ($item->category_level != '1') :
													echo ' &#171; ';
													if ($canEditParCat || $canEditOwnParCat) :
														echo '<a href="' . $ParentCatUrl . '" title="' . $EditCatTxt . '">';
													endif;
													echo $this->escape($item->parent_category_title);
													if ($canEditParCat || $canEditOwnParCat) :
														echo '</a>';
													endif;
												endif;
											}
											else
											{
												if ($item->category_level != '1') :
													if ($canEditParCat || $canEditOwnParCat) :
														echo '<a href="' . $ParentCatUrl . '" title="' . $EditCatTxt . '">';
													endif;
													echo $this->escape($item->parent_category_title);
													if ($canEditParCat || $canEditOwnParCat) :
														echo '</a>';
													endif;
													echo ' &#187; ';
												endif;
												if ($canEditCat || $canEditOwnCat) :
													echo '<a href="' . $CurrentCatUrl . '" title="' . $EditCatTxt . '">';
												endif;
												echo $this->escape($item->category_title);
												if ($canEditCat || $canEditOwnCat) :
													echo '</a>';
												endif;
											}
											?>
										</div>
									</div>
								</th>
								<td class="small d-none d-md-table-cell">
									<?php echo $this->escape($item->access_level); ?>
								</td>
								<td class="small d-none d-md-table-cell">
									<?php if ((int) $item->created_by != 0) : ?>
										<a href="<?php echo Route::_('index.php?option=com_users&task=user.edit&id=' . (int) $item->created_by); ?>">
											<?php echo $this->escape($item->author_name); ?>
										</a>
									<?php else : ?>
										<?php echo Text::_('JNONE'); ?>
									<?php endif; ?>
									<?php if ($item->created_by_alias) : ?>
										<div class="smallsub"><?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->created_by_alias)); ?></div>
									<?php endif; ?>
								</td>
								<?php if (Multilanguage::isEnabled()) : ?>
									<td class="small d-none d-md-table-cell">
										<?php echo LayoutHelper::render('joomla.content.language', $item); ?>
									</td>
								<?php endif; ?>
								<td class="small d-none d-md-table-cell text-center">
									<?php
									$date = $item->{$orderingColumn};
									echo $date > 0 ? HTMLHelper::_('date', $date, Text::_('DATE_FORMAT_LC4')) : '-';
									?>
								</td>
								<td class="d-none d-lg-table-cell text-center">
									<span class="badge badge-info">
									<?php echo (int) $item->hits; ?>
									</span>
								</td>
								<?php if ($this->vote) : ?>
									<td class="d-none d-md-table-cell text-center">
										<span class="badge badge-success" >
										<?php echo (int) $item->rating_count; ?>
										</span>
									</td>
									<td class="d-none d-md-table-cell text-center">
										<span class="badge badge-warning" >
										<?php echo (int) $item->rating; ?>
										</span>
									</td>
								<?php endif; ?>
								<td class="d-none d-lg-table-cell">
									<?php echo (int) $item->id; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>

					<?php // load the pagination. ?>
					<?php echo $this->pagination->getListFooter(); ?>

					<?php echo HTMLHelper::_(
						'bootstrap.renderModal',
						'stageModal',
						array(
							'title'  => Text::_('JTOOLBAR_CHANGE_STATUS'),
							'footer' => $this->loadTemplate('stage_footer'),
						),
						$this->loadTemplate('stage_body')
					); ?>

				<?php endif; ?>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="featured" value="1">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
