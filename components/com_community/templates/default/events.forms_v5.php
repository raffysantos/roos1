<?php

/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 * 
 * @params	categories Array	An array of categories
 */
defined('_JEXEC') or die();
?>
<style type="text/css">
div#community-wrap .calendar{
	vertical-align: middle; 
	padding-left: 4px;
	padding-right:4px; 
	border: medium none;
}
</style>

<form method="post" action="<?php echo CRoute::getURI(); ?>" id="createEvent" name="createEvent" class="community-form-validate">

<script type="text/javascript">

function formatAsDollars(amount:Number):String {

// return a 0 dollar value if amount is not valid
// (you may optionally want to return an empty string)
if (isNaN(amount)) {
return "$0.00";
}
// round the amount to the nearest 100th 
amount = Math.round(amount*100)/100;

// convert the number to a string
var amount_str:String = String(amount);

// split the string by the decimal point, separating the
// whole dollar value from the cents. Dollars are in
// amount_array[0], cents in amount_array[1]
var amount_array = amount_str.split(".");

// if there are no cents, add them using "00"
if (amount_array[1] == undefined) {
amount_array[1] = "00";
}
// if the cents are too short, add necessary "0" 
if (amount_array[1].length == 1) {
amount_array[1] += "0";
}
// add the dollars portion of the amount to an 
// array in sections of 3 to separate with commas
var dollar_array:Array = new Array();
var start:Number;
var end:Number = amount_array[0].length;
while (end>0) {
start = Math.max(end-3, 0);
dollar_array.unshift(amount_array[0].slice(start, end));
end = start;
}

// assign dollar value back in amount_array with
// the a comma delimited value from dollar_array
amount_array[0] = dollar_array.join(",");

// finally construct the return string joining
// dollars with cents in amount_array
return ("$"+amount_array.join("."));
}
_root.onEnterFrame = function() {
text_two.text = (formatAsDollars(text_one.text));
};



	
	joms.jQuery(document).ready(function(){

		joms.events.showDesc();

		joms.jQuery("#repeat option[value=" + '<?php echo $event->repeat;?>' + "]").attr("selected", "selected");
			
		<?php if ($event->id > 0 ) { ?>
			joms.jQuery('#repeat').hide();
			repeatlabel = joms.jQuery('#repeat option:selected').text();
			joms.jQuery('#repeatcontent').html(repeatlabel);
		<?php } ?>

	});
	
	joms.jQuery('#createEvent').submit(function(event) {

		<?php echo $editor->saveText( 'description' ); ?>

		// show cwindow repeat action for current / future
		<?php if ($event->id > 0 && $event->isRecurring() && $enableRepeat) { ?>
			if (joms.jQuery('#repeataction').val() == '') {
				joms.events.save();
				return false;
			}
		<?php }?>

	});
	
	

			</script>
			    

    
<div id="community-events-wrap">
<?php if(!$event->id && $eventcreatelimit != 0 ) { ?>
    <?php if($eventCreated/$eventcreatelimit>=COMMUNITY_SHOW_LIMIT) { ?>
	<div class="hints">
		<?php echo JText::sprintf('COM_COMMUNITY_EVENTS_CREATION_LIMIT_STATUS', $eventCreated, $eventcreatelimit ); ?>
	</div>
    <?php } ?>
<?php } ?>
	<table class="formtable" cellspacing="1" cellpadding="0">
        <?php echo $beforeFormDisplay;?>
	<!-- events name -->
	<tr>
		<td class="key">
			<label for="title" class="label">
				*<?php echo JText::_('COM_COMMUNITY_EVENTS_TITLE_LABEL'); ?>
			</label>
		</td>
		<td class="value">
			<input name="title" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_TITLE_TIPS'); ?>" id="title" type="text" size="45" maxlength="255" class="required inputbox jomNameTips" value="<?php echo $this->escape($event->title); ?>" />
		</td>
	</tr>
        <!--events summary-->
        <tr>
                <td class="key">
                        <label for="summary" class="label">
                            <?php echo JText::_('COM_COMMUNITY_EVENTS_SUMMARY')?>
                        </label>
                </td>
                <td class="value">
                    <textarea name="summary" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_SUMMARY_TIPS')?>" id="summary" maxlength="140" class="jomNameTips" style="width:293px;height:50px;resize:vertical;"><?php echo $this->escape($event->summary);?></textarea>
                </td>
                
        </tr>
        <tr id="event-description-link">
            <td class="key"></td>
            <td class="value"></td>
        </tr>
        
        


	<!-- events category
	<tr>
		<td class="key">
			<label for="catid" class="label">
				*<?php echo JText::_('COM_COMMUNITY_EVENTS_CATEGORY');?>
			</label>
		</td>
		<td class="value">
			<span class="jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_CATEGORY_TIPS');?>"><?php echo $lists['categoryid']; ?></span>
		</td>
	</tr>
	-->
	
	<!-- events location -->
	<tr>
		<td class="key">
			<label for="location" class="label">
				*<?php echo JText::_('COM_COMMUNITY_EVENTS_LOCATION'); ?>
			</label>
		</td>
		<td class="value">

			<span class="jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_LOCATION_TIPS');?>"><?php 
				$db = & JFactory::getDBO();
				$query = 'SELECT * FROM #__locations WHERE ClubID=5';   //ClubID='.$lists['categoryid'];	
				$db->setQuery($query);
				$row = $db->loadRowList();?>
			
				<select name="location" id="location">
				     <?php $ii=0; foreach ( $row as $option ) : ?>
				     
				     <option value="<?php echo $row[$ii][2];?>"<?php echo $event->location == $row[$ii][2] ? ' selected="selected"' : '';?>><?php echo $row[$ii][1]; ?></option>
				     
				     <?php $ii=$ii+1; endforeach; ?>
				</select>
			

			</span>



			
			<div class="small">
				<?php echo JText::_('COM_COMMUNITY_EVENTS_LOCATION_DESCRIPTION');?>
			</div>

		</td>

	</tr>
	
<!-- events Instructor -->
	<tr>			
		<td class="key">
                        <label for="InstructorID" class="label">
                            <?php echo JText::_('Instructor')?>
                        </label>
                </td>
                <td class="value">
 

			<span class="jomNameTips" title="<?php echo JText::_('Please select Instructor');?>"><?php 
				$db = & JFactory::getDBO();
				$query = 'SELECT * FROM #__users WHERE 1';	
				$db->setQuery($query);
				$row = $db->loadRowList();?>
			
				<select name="InstructorID">
				     <?php $ii=0; foreach ( $row as $option ) : ?>
				     
				     	<option value="<?php echo $row[$ii][0];?>"<?php echo $event->InstructorID == $row[$ii][0] ? ' selected="selected"' : '';?>><?php echo $row[$ii][1]; ?></option>
				     

				     <?php $ii=$ii+1; endforeach; ?>
				</select>
			

			</span>
 		</td>
 	</tr>
 
<!-- events Hourly Rate -->
	<tr>			
		<td class="key">
                        <label for="HourlyRate" class="label">
                            <?php echo JText::_('Hourly Rate $')?>
                        </label>
                </td>
                <td class="value">
 

			<span class="jomNameTips" title="<?php echo JText::_('Please enter the Hourly Rate in AUD');?>"><?php 
				$db = & JFactory::getDBO();
				$query = 'SELECT * FROM #__users WHERE 1';	
				$db->setQuery($query);
				$row = $db->loadRowList();?>
			
				
				<input name="HourlyRate" title="<?php echo JText::_('Please enter the Hourly Rate in AUD'); ?>" id="HourlyRate" min="0" max="99999" step="10" size="5"
			
				
				type="number" size="30" class="inputbox jomNameTips" value="<?php echo money_format('%i',$this->escape($event->HourlyRate));  ?>" />   


				</td>
			

			</span>
 		</td>
 	</tr>


	<!-- events location -->
	<!-- events start datetime -->
	<tr  id="event-start-datetime">
		<td class="key">
			<label class="label">
				*<?php echo JText::_('COM_COMMUNITY_EVENTS_START_TIME'); ?>
			</label>
		</td>
		<td class="value">			
			<span class="jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_START_TIME_TIPS'); ?>">
				<script type="text/javascript">
					<!-- add calendar listener to the field -->
					window.addEvent('domready', function() {Calendar.setup({
					inputField: "startdate",
					ifFormat: "%Y-%m-%d",
					button: "startdate",
					singleClick: true,
					firstDay: 0
					});}); 
				</script>
				<?php echo JHTML::_('calendar',  $startDate->toFormat( '%Y-%m-%d' ) , 'startdate', 'startdate', '%Y-%m-%d', array('class'=>'required inputbox', 'size'=>'10',  'maxlength'=>'10' , 'readonly' => 'true', 'onchange' => 'updateEndDate();', 'id'=>'startdate') );?>
				<span id="start-time">
				<?php echo $startHourSelect; ?>:<?php  echo $startMinSelect; ?> <?php echo $startAmPmSelect;?>
				</span>
				<script type="text/javascript">
					function updateEndDate(){
						var startdate	=   joms.jQuery('#startdate').val();
						var enddate		=   joms.jQuery('#enddate').val();
						var repeatend	=   joms.jQuery('#repeatend').val();
						
						tmpenddate		=	new Date(enddate);
						tmpstartdate	=   new Date(startdate);
						tmprepeatend    =   new Date(repeatend);
						
						if(tmpenddate < tmpstartdate){
						    joms.jQuery('#enddate').val( startdate );
						}
						
						if(tmprepeatend < tmpstartdate){
						    joms.jQuery('#repeatend').val( startdate );
						}
					}
				</script>
			</span>
		</td>
	</tr>
	<!-- events end datetime -->
	<tr id="event-end-datetime">
		<td class="key">
			<label class="label">
				*<?php echo JText::_('COM_COMMUNITY_EVENTS_END_TIME'); ?>
			</label>
		</td>
		<td class="value">			
			<span class="jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_END_TIME_TIPS'); ?>">
				<script type="text/javascript">
					window.addEvent('domready', function() {Calendar.setup({
					inputField: "enddate",
					ifFormat: "%Y-%m-%d",
					button: "enddate",
					singleClick: true,
					firstDay: 0
					});}); 
				</script>
				<?php echo JHTML::_('calendar',  $endDate->toFormat( '%Y-%m-%d' ) , 'enddate', 'enddate', '%Y-%m-%d', array('class'=>'required inputbox', 'size'=>'10',  'maxlength'=>'10' , 'readonly' => 'true', 'id'=>'enddate', 'onchange' => 'updateStartDate();') );?>
				<span id="end-time">
				<?php echo $endHourSelect; ?>:<?php echo $endMinSelect; ?> <?php echo $endAmPmSelect;?>
				<script type="text/javascript">
					function updateStartDate(){
						var enddate	=   joms.jQuery('#enddate').val();
						var startdate	=   joms.jQuery('#startdate').val();
						var repeatend	=   joms.jQuery('#repeatend').val();

						tmpenddate		=	new Date(enddate);
						tmpstartdate	=   new Date(startdate);
						tmprepeatend    =   new Date(repeatend);

						if(tmpenddate < tmpstartdate){
						    joms.jQuery('#startdate').val( enddate );
						}
						
						if(tmprepeatend < tmpenddate){
						    joms.jQuery('#repeatend').val( enddate );
						}
					}
				</script>
				</span>
			</span>
		</td>
	</tr>
	<script type="text/javascript">
		function toggleEventDateTime()
		{
			if( joms.jQuery('#allday').attr('checked') == 'checked' ){
				joms.jQuery('#start-time, #end-time').hide();
			}else{
				joms.jQuery('#start-time, #end-time').show();
			}
		}

		function toggleEventRepeat()
		{
			if( joms.jQuery('#repeat').val() != '' ){
				joms.jQuery('#repeatendinput').show();
				limitdesc = '';
				if (joms.jQuery('#repeat').val() == 'daily') {
					limitdesc = '<?php echo addslashes(sprintf(Jtext::_('COM_COMMUNITY_EVENTS_REPEAT_LIMIT_DESC'), COMMUNITY_EVENT_RECURRING_LIMIT_DAILY));?>';
				}else if (joms.jQuery('#repeat').val() == 'weekly') {
					limitdesc = '<?php echo addslashes(sprintf(Jtext::_('COM_COMMUNITY_EVENTS_REPEAT_LIMIT_DESC'), COMMUNITY_EVENT_RECURRING_LIMIT_WEEKLY));?>';
				}else if (joms.jQuery('#repeat').val() == 'monthly') {
					limitdesc = '<?php echo addslashes(sprintf(Jtext::_('COM_COMMUNITY_EVENTS_REPEAT_LIMIT_DESC'), COMMUNITY_EVENT_RECURRING_LIMIT_MONTHLY));?>';
				}
				joms.jQuery('#repeatlimitdesc').html(limitdesc);
				joms.jQuery('#repeatlimitdesc').show();
			}else{
				joms.jQuery('#repeatendinput').hide();
				joms.jQuery('#repeatlimitdesc').hide();
			}
		}
	</script>
	<tr>
		<td class="key">&nbsp;</td>
		<td class="value">
			<span class="jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_ALL_DAY_TIPS');?>">
				<input id="allday" name="allday" type="checkbox" onclick="toggleEventDateTime();" value="1" <?php if($event->allday){ echo 'checked'; } ?> />&nbsp;<?php echo JText::_('COM_COMMUNITY_EVENTS_ALL_DAY'); ?>
			</span>
		</td>
	</tr>
        <?php if ($enableRepeat) { ?>
	<tr>
		<td class="key">
			<label for="repeat" class="label">
				*<?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT'); ?>
			</label>
		</td>
		<td class="value">
			<span class="jomNameTips" original-title="<?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_TIPS'); ?>">
			<span id="repeatcontent"></span>
			<select name="repeat" id="repeat" onChange="toggleEventRepeat()">
				<option value=""><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_NONE'); ?></option>
				<option value="daily"><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_DAILY'); ?></option>
				<option value="weekly"><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_WEEKLY'); ?></option>
				<option value="monthly"><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_MONTHLY'); ?></option>
			</select>
			</span>

			<span id="repeatendinput">
			<span class="label">&nbsp;&nbsp;*<?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_END'); ?>&nbsp;</span>
			<span class="jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_END_TIPS'); ?>">
				<script type="text/javascript">
					<!-- add calendar listener to the field -->
					window.addEvent('domready', function() {Calendar.setup({
					inputField: "repeatend",
					ifFormat: "%Y-%m-%d",
					button: "repeatend",
					singleClick: true,
					firstDay: 0
					});});
				</script>
				<?php 
                                
                                if (!strtotime($event->repeatend)  || $event->repeatend == '0000-00-00' ) {
                                    $repeatend = null;
                                } else {
                                    $repeatend = $repeatEndDate->toFormat( '%Y-%m-%d' );
                                }
                                
                                echo JHTML::_('calendar',  $repeatend , 'repeatend', 'repeatend', '%Y-%m-%d', array('class'=>'required inputbox', 'size'=>'10',  'maxlength'=>'10' , 'readonly' => 'true', 'id'=>'repeatend', 'onchange' => 'updateEventDate();') );?>
				<script type="text/javascript">
					function updateEventDate(){
						var enddate		=   joms.jQuery('#enddate').val();
						var startdate	=   joms.jQuery('#startdate').val();
						var repeatend	=   joms.jQuery('#repeatend').val();

						tmpenddate		=	new Date(enddate);
						tmpstartdate	=   new Date(startdate);
						tmprepeatend    =   new Date(repeatend);

						if(tmprepeatend < tmpstartdate){
						    joms.jQuery('#startdate').val( repeatend );
						}
						
						if(tmprepeatend < tmpenddate){
						    joms.jQuery('#enddate').val( repeatend );
						}
					}
				</script>
			</span>
			</span>
			<div class="small" id="repeatlimitdesc"></div>
		</td>
	</tr>
        <?php } ?>
        
	<?php
	if( $config->get('eventshowtimezone') )
	{
	?>
	<tr>
		<td class="key">
			<label class="label">
				*<?php echo JText::_('COM_COMMUNITY_TIMEZONE'); ?>
			</label>
		</td>
		<td class="value">			
			<span class="jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_EVENTS_SET_TIMEZONE'); ?>">
				<select name="offset">
				<?php
				$defaultTimeZone = isset($event->offset)?$event->offset:$systemOffset;				
				foreach( $timezones as $offset => $value ){
				?>
					<option value="<?php echo $offset;?>"<?php echo $defaultTimeZone == $offset ? ' selected="selected"' : '';?>><?php echo $value;?></option>
				<?php
				}
				?>
				</select>
			</span>
		</td>
	</tr>
	<?php
	}
	?>
	<!-- events tickets -->
	<tr>
		
		<td class="value">
			<input title="" name="ticket" id="ticket" type="hidden" size="10" maxlength="5" class="required inputbox jomNameTips" value="<?php echo (empty($event->ticket)) ? '0' : $this->escape($event->ticket); ?>" />

		</td>
	</tr>	
	<?php
	if( $helper->hasPrivacy() )
	{
	?>

	<?php
	}
	?>
	<?php
	if( $helper->hasInvitation() )
	{
	?>	

	<?php
	}
	?>
        
        <?php echo $afterFormDisplay;?>
        
	<tr>
			<td class="key"></td>
			<td class="value"><span class="hints"><?php echo JText::_( 'COM_COMMUNITY_REGISTER_REQUIRED_FILEDS' ); ?></span></td>
		</tr>
	
	<!-- event buttons -->
	<tr>
		<td class="key"></td>
		<td class="value">
			<?php echo JHTML::_( 'form.token' ); ?>
			<?php if(!$event->id): ?>
			<input name="action" type="hidden" value="save" />
			<?php endif;?>
			<input type="hidden" name="eventid" value="<?php echo $event->id;?>" />
			<input type="hidden" name="repeataction" id="repeataction" value="" />
			<input type="submit" value="<?php echo ($event->id) ? JText::_('COM_COMMUNITY_SAVE_BUTTON') : JText::_('COM_COMMUNITY_EVENTS_CREATE_BUTTON');?>" class="button validateSubmit" />
			<input type="button" class="button" onclick="history.go(-1);return false;" value="<?php echo JText::_('COM_COMMUNITY_CANCEL_BUTTON');?>" />
		</td>
	</tr>
	</table>
</div>
</form>
<script type="text/javascript">
	cvalidate.init();
	cvalidate.setSystemText('REM','<?php echo addslashes(JText::_("COM_COMMUNITY_ENTRY_MISSING")); ?>');
	cvalidate.noticeTitle	= '<?php echo addslashes(JText::_('COM_COMMUNITY_NOTICE') );?>';
	
	/*
		The calendar.js does not display properly under IE when a page has been
		scrolled down. This behaviour is present everywhere within the Joomla site.
		We are injecting our fixes into their code by adding the following
		at the end of the fixPosition() function:
		if (joms.jQuery(el).parents('#community-wrap').length>0)
		{
			var anchor   = joms.jQuery(el);
			var calendar = joms.jQuery(self.element);
			box.x = anchor.offset().left - calendar.outerWidth() + anchor.outerWidth();
			box.y = anchor.offset().top - calendar.outerHeight();
		}
		Unobfuscated version of "JOOMLA/media/system/js/calendar.js" was taken from
		http://www.dynarch.com/static/jscalendar-1.0/calendar.js for reference.		
	*/
	joms.jQuery(document).ready(function()
	{
		Calendar.prototype.showAtElement=function(c,d){var a=this;var e=Calendar.getAbsolutePos(c);if(!d||typeof d!="string"){this.showAt(e.x,e.y+c.offsetHeight);return true}function b(j){if(j.x<0){j.x=0}if(j.y<0){j.y=0}var l=document.createElement("div");var i=l.style;i.position="absolute";i.right=i.bottom=i.width=i.height="0px";document.body.appendChild(l);var h=Calendar.getAbsolutePos(l);document.body.removeChild(l);if(Calendar.is_ie){h.y+=document.body.scrollTop;h.x+=document.body.scrollLeft}else{h.y+=window.scrollY;h.x+=window.scrollX}var g=j.x+j.width-h.x;if(g>0){j.x-=g}g=j.y+j.height-h.y;if(g>0){j.y-=g}if(joms.jQuery(c).parents("#community-wrap").length>0){var f=joms.jQuery(c);var k=joms.jQuery(a.element);j.x=f.offset().left-k.outerWidth()+f.outerWidth();j.y=f.offset().top-k.outerHeight()}}this.element.style.display="block";Calendar.continuation_for_the_fucking_khtml_browser=function(){var f=a.element.offsetWidth;var i=a.element.offsetHeight;a.element.style.display="none";var g=d.substr(0,1);var j="l";if(d.length>1){j=d.substr(1,1)}switch(g){case"T":e.y-=i;break;case"B":e.y+=c.offsetHeight;break;case"C":e.y+=(c.offsetHeight-i)/2;break;case"t":e.y+=c.offsetHeight-i;break;case"b":break}switch(j){case"L":e.x-=f;break;case"R":e.x+=c.offsetWidth;break;case"C":e.x+=(c.offsetWidth-f)/2;break;case"l":e.x+=c.offsetWidth-f;break;case"r":break}e.width=f;e.height=i+40;a.monthsCombo.style.display="none";b(e);a.showAt(e.x,e.y)};if(Calendar.is_khtml){setTimeout("Calendar.continuation_for_the_fucking_khtml_browser()",10)}else{Calendar.continuation_for_the_fucking_khtml_browser()}};	
		toggleEventDateTime();
		toggleEventRepeat();
	});
</script>