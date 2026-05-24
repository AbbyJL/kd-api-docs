$(function() {
    var audio = $('#music_media');
    audio[0].play();
    $("#music_audio_btn").bind('click', function() {
		$(this).hasClass("music_off") ? ($(this).addClass("music_play_yinfu").removeClass("music_off"), $("#music_yinfu").addClass("music_rotate"), $("#music_media")[0].play()) : ($(this).addClass("music_off").removeClass("music_play_yinfu"), $("#music_yinfu").removeClass("music_rotate"), $("#music_media")[0].pause());
    });
}); 
