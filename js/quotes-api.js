const API_URL = 'api/rock-quotes.php';

async function loadRandomQuote() {
    const loading = document.getElementById('quoteLoading');
    const quoteText = document.getElementById('quoteText');
    const quoteAuthor = document.getElementById('quoteAuthor');
    const quoteBand = document.getElementById('quoteBand');
    
    if (loading) loading.style.display = 'block';
    if (quoteText) quoteText.style.display = 'none';
    if (quoteAuthor) quoteAuthor.style.display = 'none';
    if (quoteBand) quoteBand.style.display = 'none';
    
    try {
        const response = await fetch(`${API_URL}?random=1`);
        const result = await response.json();
        
        if (result.success && result.data) {
            if (quoteText) quoteText.textContent = result.data.quote;
            if (quoteAuthor) quoteAuthor.textContent = `— ${result.data.author}`;
            if (quoteBand) quoteBand.textContent = result.data.band;
            
            if (loading) loading.style.display = 'none';
            if (quoteText) quoteText.style.display = 'block';
            if (quoteAuthor) quoteAuthor.style.display = 'block';
            if (quoteBand) quoteBand.style.display = 'block';
        } else {
            if (quoteText) quoteText.textContent = 'Рок живёт в каждом из нас!';
            if (quoteAuthor) quoteAuthor.textContent = '— Electric Dough';
            if (quoteBand) quoteBand.textContent = '';
            if (loading) loading.style.display = 'none';
            if (quoteText) quoteText.style.display = 'block';
            if (quoteAuthor) quoteAuthor.style.display = 'block';
        }
    } catch (error) {
        if (quoteText) quoteText.textContent = 'Музыка — это язык, который понимают все.';
        if (quoteAuthor) quoteAuthor.textContent = '— Рок-легенда';
        if (quoteBand) quoteBand.textContent = '';
        if (loading) loading.style.display = 'none';
        if (quoteText) quoteText.style.display = 'block';
        if (quoteAuthor) quoteAuthor.style.display = 'block';
    }
}

function initQuotesBlock() {
    const nextBtn = document.getElementById('nextQuoteBtn');
    if (nextBtn) {
        nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            loadRandomQuote();
            nextBtn.style.transform = 'scale(0.95)';
            setTimeout(() => { nextBtn.style.transform = ''; }, 200);
        });
    }
    loadRandomQuote();
}

document.addEventListener('DOMContentLoaded', initQuotesBlock);