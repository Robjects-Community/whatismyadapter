function loadYouTubeVideo(button) {
    const container = button.closest('.youtube-embed');
    const videoId = button.dataset.videoId;
    const maxWidth = container.getAttribute('style') ? 
                     parseInt(container.style.width) : 
                     800; // default max width

    // Create player container div
    const playerContainer = document.createElement('div');
    playerContainer.className = 'youtube-player-container';
    playerContainer.style.maxWidth = maxWidth + 'px';

    // Create iframe with privacy-enhanced mode
    const iframe = document.createElement('iframe');
    iframe.setAttribute('src', `https://www.youtube-nocookie.com/embed/${videoId}`);
    iframe.setAttribute('frameborder', '0');
    iframe.setAttribute('allowfullscreen', '');

    // Add iframe to player container
    playerContainer.appendChild(iframe);

    // Replace the placeholder with the player container
    container.innerHTML = '';
    container.appendChild(playerContainer);

    // Store consent in localStorage
    localStorage.setItem('youtube_consent', 'granted');
}