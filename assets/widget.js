(function() {
    // Determine the base URL of the Release Notes CMS from the script's own src attribute
    const scripts = document.getElementsByTagName('script');
    let cmsUrl = 'https://your-cms-domain.com'; // Fallback
    
    for (let script of scripts) {
        if (script.src && script.src.includes('widget.js')) {
            const urlObj = new URL(script.src);
            cmsUrl = urlObj.origin + urlObj.pathname.substring(0, urlObj.pathname.indexOf('/assets/'));
            break;
        }
    }

    // Fetch latest release status from the API
    fetch(cmsUrl + '/api/releases.php')
        .then(response => response.json())
        .then(data => {
            if (!data.success || data.latest_id === 0) return;

            const latestId = data.latest_id;
            const latestType = data.type;
            const seenId = localStorage.getItem('rn_seen_id');
            const hasUnread = seenId != latestId;

            // Create Widget Button (Bell / What's New icon)
            const widgetBtn = document.createElement('div');
            widgetBtn.id = 'rn-widget-btn';
            widgetBtn.innerHTML = `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                <span>What's New</span>
                ${hasUnread ? '<span class="rn-badge-dot"></span>' : ''}
            `;

            // Create Modal Overlay containing the iframe
            const modal = document.createElement('div');
            modal.id = 'rn-widget-modal';
            modal.innerHTML = `
                <div class="rn-modal-dialog">
                    <div class="rn-modal-header">
                        <h3>Release Notes</h3>
                        <button class="rn-close-btn">&times;</button>
                    </div>
                    <iframe src="${cmsUrl}/index.php?embed=1" frameborder="0"></iframe>
                </div>
            `;

            document.body.appendChild(widgetBtn);
            document.body.appendChild(modal);

            // Inject Widget Styles
            const style = document.createElement('style');
            style.innerHTML = `
                #rn-widget-btn {
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    background: #2563eb;
                    color: white;
                    padding: 10px 16px;
                    border-radius: 30px;
                    font-family: sans-serif;
                    font-size: 14px;
                    font-weight: bold;
                    cursor: pointer;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    z-index: 99999;
                    transition: background 0.2s;
                }
                #rn-widget-btn:hover { background: #1d4ed8; }
                .rn-badge-dot {
                    width: 10px;
                    height: 10px;
                    background: #dc3545;
                    border-radius: 50%;
                    display: inline-block;
                }
                #rn-widget-modal {
                    display: none;
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.5);
                    z-index: 100000;
                    justify-content: center;
                    align-items: center;
                }
                .rn-modal-dialog {
                    background: white;
                    width: 90%;
                    max-width: 600px;
                    height: 80vh;
                    border-radius: 8px;
                    display: flex;
                    flex-direction: column;
                    overflow: hidden;
                    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                }
                .rn-modal-header {
                    padding: 12px 20px;
                    background: #f8f9fa;
                    border-bottom: 1px solid #dee2e6;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .rn-modal-header h3 { margin: 0; font-size: 16px; font-family: sans-serif; color: #333; }
                .rn-close-btn { background: none; border: none; font-size: 24px; cursor: pointer; color: #666; }
                #rn-widget-modal iframe { width: 100%; height: 100%; border: none; }
            `;
            document.head.appendChild(style);

            // Toggle Modal Open/Close & Mark as Read
            widgetBtn.addEventListener('click', () => {
                modal.style.display = 'flex';
                localStorage.setItem('rn_seen_id', latestId);
                const dot = widgetBtn.querySelector('.rn-badge-dot');
                if (dot) dot.remove();
            });

            modal.querySelector('.rn-close-btn').addEventListener('click', () => {
                modal.style.display = 'none';
            });

            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.style.display = 'none';
            });
        })
        .catch(err => console.error('Error loading release notes widget:', err));
})();
