<div class="gtsapi-gallery">
    {if $files}
        <div class="gtsapi-gallery-items">
            {foreach $files as $file}
                <div class="gtsapi-gallery-item" data-id="{$file.id}" data-type="{$file.type}">
                    <div class="gtsapi-gallery-preview">
                        {if $file.type in ['image', 'jpg', 'jpeg', 'png', 'gif', 'webp']}
                            <img src="{$file.medium ?: $file.url}" alt="{$file.name}" 
                                 data-large="{$file.large ?: $file.url}"
                                 class="gtsapi-gallery-image" />
                        {else}
                            <div class="gtsapi-gallery-file-icon">
                                <img src="{$file.thumbnail}" alt="{$file.type}" />
                            </div>
                        {/if}
                    </div>
                    
                    <div class="gtsapi-gallery-info">
                        {* <div class="gtsapi-gallery-name" title="{$file.name}">
                            {$file.name}
                        </div>
                        
                        {if $file.description}
                            <div class="gtsapi-gallery-description">
                                {$file.description}
                            </div>
                        {/if}
                        
                        <div class="gtsapi-gallery-meta">
                            <span class="gtsapi-gallery-size">{$file.size_formatted}</span>
                            {if $file.createdon_formatted}
                                <span class="gtsapi-gallery-date">{$file.createdon_formatted}</span>
                            {/if}
                        </div> *}
                        
                        <div class="gtsapi-gallery-actions">
                            
                            {if $file.type in ['image', 'jpg', 'jpeg', 'png', 'gif', 'webp']}
                                <button type="button" class="gtsapi-gallery-view" 
                                        data-url="{$file.large ?: $file.url}"
                                        title="Просмотр">
                                    Просмотр
                                </button>
                            {/if}
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    {else}
        <div class="gtsapi-gallery-empty">
            Файлы не найдены
        </div>
    {/if}
</div>

<style>
.gtsapi-gallery {
    margin: 20px 0;
}

.gtsapi-gallery-items {
    display: grid;
    {* grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); *}
    gap: 20px;
}

.gtsapi-gallery-item {
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
    transition: box-shadow 0.3s ease;
}

.gtsapi-gallery-item:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.gtsapi-gallery-preview {
    position: relative;
    height: 200px;
    overflow: hidden;
    background: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
}

.gtsapi-gallery-image {
    max-width: 100%;
    max-height: 100%;
    object-fit: cover;
    cursor: pointer;
}

.gtsapi-gallery-file-icon img {
    width: 48px;
    height: 48px;
}

.gtsapi-gallery-info {
    padding: 15px;
}

.gtsapi-gallery-name {
    font-weight: bold;
    margin-bottom: 8px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.gtsapi-gallery-description {
    color: #666;
    font-size: 14px;
    margin-bottom: 10px;
    line-height: 1.4;
}

.gtsapi-gallery-meta {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #999;
    margin-bottom: 10px;
}

.gtsapi-gallery-actions {
    display: flex;
    gap: 10px;
}

.gtsapi-gallery-download,
.gtsapi-gallery-view {
    padding: 6px 12px;
    font-size: 12px;
    text-decoration: none;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #fff;
    color: #333;
    cursor: pointer;
    transition: all 0.3s ease;
}

.gtsapi-gallery-download:hover,
.gtsapi-gallery-view:hover {
    background: #f0f0f0;
    text-decoration: none;
}

.gtsapi-gallery-empty {
    text-align: center;
    padding: 40px;
    color: #999;
    font-style: italic;
}

/* Модальное окно для просмотра изображений */
.gtsapi-gallery-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.8);
}

.gtsapi-gallery-modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    max-width: 90%;
    max-height: 90%;
}

.gtsapi-gallery-modal img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.gtsapi-gallery-modal-close {
    position: absolute;
    top: 15px;
    right: 35px;
    color: #fff;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Создаем модальное окно для просмотра изображений
    const modal = document.createElement('div');
    modal.className = 'gtsapi-gallery-modal';
    modal.innerHTML = `
        <span class="gtsapi-gallery-modal-close">&times;</span>
        <div class="gtsapi-gallery-modal-content">
            <img src="" alt="">
        </div>
    `;
    document.body.appendChild(modal);
    
    const modalImg = modal.querySelector('img');
    const closeBtn = modal.querySelector('.gtsapi-gallery-modal-close');
    
    // Обработчики для кнопок просмотра
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('gtsapi-gallery-view')) {
            const url = e.target.getAttribute('data-url');
            modalImg.src = url;
            modal.style.display = 'block';
        }
        
        if (e.target.classList.contains('gtsapi-gallery-image')) {
            const url = e.target.getAttribute('data-large') || e.target.src;
            modalImg.src = url;
            modal.style.display = 'block';
        }
    });
    
    // Закрытие модального окна
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Закрытие по ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'block') {
            modal.style.display = 'none';
        }
    });
});
</script>
