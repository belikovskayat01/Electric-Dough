const API_RANDOM_PRODUCT = 'api/random-product.php';

async function playRiffGame() {
    const welcome = document.getElementById('riffWelcome');
    const loader = document.getElementById('riffLoader');
    const result = document.getElementById('riffResult');
    const startBtn = document.getElementById('startRiffBtn');
    const container = document.getElementById('riffGameContainer');
    
    if (welcome) welcome.style.display = 'none';
    if (loader) loader.style.display = 'flex';
    if (result) result.style.display = 'none';
    if (container) container.classList.add('active');
    
    if (startBtn) {
        startBtn.style.transform = 'scale(0.95)';
        setTimeout(() => {
            if (startBtn) startBtn.style.transform = '';
        }, 200);
    }
    
    try {
        const response = await fetch(API_RANDOM_PRODUCT);
        const data = await response.json();
        
        if (data.success && data.product) {
            const product = data.product;
            
            document.getElementById('riffProductName').textContent = product.name;
            document.getElementById('riffProductCategory').textContent = product.category;
            document.getElementById('riffProductPrice').textContent = `${product.price} ₽`;
            document.getElementById('riffProductDescription').textContent = product.description;
            
            const productImage = document.getElementById('riffProductImage');
            productImage.src = product.image;
            productImage.alt = product.name;
            productImage.onerror = function() {
                this.src = 'IMG/placeholder.jpg';
            };
            
            const orderLink = document.getElementById('riffOrderLink');
            orderLink.href = `pre-order.php?product_id=${product.id}`;
            
            setTimeout(() => {
                if (loader) loader.style.display = 'none';
                if (result) {
                    result.style.display = 'block';
                    result.style.animation = 'riffReveal 0.5s ease';
                }
                if (container) {
                    container.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }, 800);
            
        } else {
            throw new Error('Не удалось загрузить товар');
        }
    } catch (error) {
        console.error('Ошибка:', error);
        if (loader) loader.style.display = 'none';
        if (result) {
            result.style.display = 'block';
            document.getElementById('riffProductName').textContent = 'Упс! Что-то пошло не так';
            document.getElementById('riffProductDescription').textContent = 'Попробуйте снова через мгновение...';
            document.getElementById('riffProductPrice').textContent = '';
            document.getElementById('riffProductCategory').textContent = '';
        }
    }
}

function resetRiffGame() {
    const welcome = document.getElementById('riffWelcome');
    const loader = document.getElementById('riffLoader');
    const result = document.getElementById('riffResult');
    
    if (welcome) welcome.style.display = 'flex';
    if (loader) loader.style.display = 'none';
    if (result) result.style.display = 'none';
}

function initRiffGame() {
    const startBtn = document.getElementById('startRiffBtn');
    const playAgainBtn = document.getElementById('riffPlayAgain');
    
    resetRiffGame();
    
    if (startBtn) {
        startBtn.addEventListener('click', (e) => {
            e.preventDefault();
            playRiffGame();
        });
    }
    
    if (playAgainBtn) {
        playAgainBtn.addEventListener('click', (e) => {
            e.preventDefault();
            playRiffGame();
        });
    }
}

document.addEventListener('DOMContentLoaded', initRiffGame);