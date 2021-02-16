

		$(function() {
			var col, el;
			
			$("input[type=radio]").click(function() {
			   el = $(this);
			   col = el.data("col");
			   $("input[data-col=" + col + "]").prop("checked", false);
			   el.prop("checked", true);
			});
		});
		
		
		$(document).ready(function(){
			var submitted = false;
			$("#btn1").click(function(){
				$("#btn1").attr('disabled','disabled')
				if(submitted)
				{				
					$("#results").text("Hai già risposto!");				
					return;
				}
				
				resText = '';
				best = [];
				worst = [];
				$('input:radio:checked').each(function() {
					//this loops through all checked radio buttons
					//you can use the radio button using $(this)
					system = $(this).attr("system");
					sent = $(this).attr("sent"); 
					// best (1) or worst (0)? 
					vote = parseInt( $(this).attr("data-col") );
					if(vote % 2 == 0) // worst
					{
						worst[sent] = system;
					}
					else // best
					{
						best[sent] = system;
					}
					//resText += ' '+ $(this).attr("name") + ' ' + $(this).attr("data-col");
					
				});
				
				/*
				for(var i = 1; i < best.length; i++) {
						resText += 'Good votes for system '+i+ '='+ best[i]+' '; 
				}
				for(var i = 1; i < worst.length; i++) {
						resText += 'Bad votes for system '+i+ '='+ worst[i]+' '; 
				}
				*/
				var sents = [];
				for(var i = 0; i < best.length; i++) {
					sent = { "best" : best[i] , "worst" : worst[i] };
					sents.push(sent);
				}
		
				var surveyId = $('#myForm').attr("surveyId");
				var pdata = { id: surveyId, resp : sents };
				
				
				$( "#postdata" ).text( JSON.stringify(pdata));
				
				var posting = $.post( 'processresponse.php', JSON.stringify(pdata) );
				
				
				
				// Put the results in a div
				posting.done(function( data ) {
					$( "#results" ).empty().append( data );
				});
				
				submitted = true;
	
			});
		});