
	var selectAddr = {
		init : function(){
			var result = selectAddr.creatOption( address );
			$('#city').html( result );
			selectAddr.addOption( 'city', address[0].dataAdd );

			changeEle( 'city', address, function( data ){
				changeEle( 'school_name', data, function( data ){
					changeEle( 'school_area', data);
				} );
			} );


			$("#submit").click(function(){
				for( var i = 0,len = $("option").length; i < len; i ++ ){
					var value = $('option').eq(i).val();
					var result =  value.substring ( value.indexOf(',')+1 );
					$('option').eq(i).val( result );
				}
				return true;
			});


			function changeEle( id, data, callBack ){
				$('#'+ id ).change(function(){
					var index = parseInt(  $(this).val() );
					selectAddr.addOption( id, data[ index ].dataAdd );
					if( callBack )
						callBack( data[ index ].dataAdd );
				});
				if( callBack )
					callBack( data[ 0 ].dataAdd );
			}
		},
		creatOption : function( msg ){
			var result = '';
			for( var i = 0; i < msg.length; i++ ){
				result += '<option value="'+ i + ',' + msg[i].name +'">'+ msg[i].name +'</option>';
			}
			return result;
		},
		changeEle : function( id, data, childId ){
			$('#'+ id ).change(function(){
				var index = parseInt(  $(this).val() );
				var result = selectAddr.creatOption( data[ index ].dataAdd );
				$('#'+childId).html( result );
			});
		},
		addOption : function ( id, data ){
			var result = '';
			switch( id ){
				case 'city' : 
					result = selectAddr.creatOption( data );
					$('#school_name').html( result );
					data = data[0].dataAdd;
				case 'school_name' : 
					if( data[0].name == 'hidden' ){
						$('#school_area').parent().hide();
					} else {
						$('#school_area').parent().show();
					}
					result = selectAddr.creatOption( data );
					$('#school_area').html( result );
					data = data[0].dataAdd;
				case 'school_area' :
					result = selectAddr.creatOption( data );
					$('#dormitory').html( result );
			}
		}
	};