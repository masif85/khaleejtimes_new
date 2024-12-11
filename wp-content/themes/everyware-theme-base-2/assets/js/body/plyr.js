$(function() {
  const Plyr = require('plyr');
  const youtubeVideos = document.querySelectorAll('.video__youtube');
  youtubeVideos.forEach(video => {
    Plyr.setup(`#${video.id}`, {
      // See all available paramaters at
      // https://developers.google.com/youtube/player_parameters#Parameters
      youtube: {
        start: video.dataset.start,
        enablejsapi: 0,
        modestbranding: 1
      }
    });
  })
  Plyr.setup('.video__vimeo', {
      // See all available paramaters at
      // https://github.com/vimeo/player.js/#embed-options
      vimeo: {}
  })
});
