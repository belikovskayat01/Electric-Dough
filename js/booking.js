document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('bookingForm');
    const submitBtn = document.getElementById('submitBtn');

    const phoneInput = document.getElementById('phone');
    const dateInput = document.getElementById('booking_date');
    const timeInput = document.getElementById('booking_time');

    const tableModal = document.getElementById('table-modal');

    const openModalBtn = document.getElementById('openTableModal');
    const closeModalBtn = document.getElementById('closeTableModal');

    const confirmBtn = document.getElementById('confirmTableBtn');
    const cancelBtn = document.getElementById('cancelTableBtn');

    const anyTableBtn = document.getElementById('anyTableBtn');

    const selectedTableInput = document.getElementById('selected_table_id');

    const tableSelectionButtons = document.getElementById('table_selection_buttons');
    const selectedTableDisplay = document.getElementById('selected_table_display');
    const selectedTableNumberDisplay = document.getElementById('selected_table_number_display');
    const changeTableBtn = document.getElementById('changeTableBtn');
    const clearTableBtn = document.getElementById('clearTableSelection');

    let currentSelectedTable = null;
    let currentSelectedTableNumber = null;
    let currentSelectedChair = null;
    let currentSelectedChairType = null;

    const chairsConfig = {
        1: { top: 2, left: 0, right: 1, bottom: 2 },
        2: { top: 0, left: 1, right: 1, bottom: 2 },
        3: { top: 2, left: 1, right: 0, bottom: 2 },
        4: { top: 2, left: 1, right: 1, bottom: 2 },
        5: { top: 2, left: 1, right: 1, bottom: 2 },
        6: { top: 2, left: 0, right: 1, bottom: 2 },
        7: { top: 2, left: 0, right: 0, bottom: 2 },
        8: { top: 2, left: 0, right: 0, bottom: 2 },
        9: { top: 2, left: 1, right: 0, bottom: 2 }
    };

    const tableCapacity = {
        1: 5,   
        2: 4,   
        3: 5,   
        4: 6,   
        5: 6,   
        6: 5,   
        7: 4,   
        8: 4,   
        9: 5,   
        10: 1, 11: 1, 12: 1, 13: 1, 14: 1, 15: 1, 16: 1, 17: 1, 18: 1, 19: 1
    };

    function updateGuestsByTable(tableNumber) {
        const guestsSelect = document.getElementById('guests');
        if (!guestsSelect) return;
        
        const capacity = tableCapacity[tableNumber] || 4;
        
        if (capacity >= 1 && capacity <= 6) {
            guestsSelect.value = capacity;

            const event = new Event('change');
            guestsSelect.dispatchEvent(event);
        }
    }

  function updateSelectedTableDisplay() {
    if (currentSelectedTable && currentSelectedTableNumber) {
        if (tableSelectionButtons) tableSelectionButtons.style.display = 'none';
        if (selectedTableDisplay) selectedTableDisplay.style.display = 'block';
        
        let displayText = `${currentSelectedTableNumber}`;
        if (currentSelectedChair) {
            const chairTypeText = {
                'top': 'верхнее',
                'left': 'левое',
                'right': 'правое',
                'bottom': 'нижнее'
            };
            displayText += ` (${chairTypeText[currentSelectedChairType]} место)`;
        }
        if (selectedTableNumberDisplay) selectedTableNumberDisplay.textContent = displayText;
        if (selectedTableInput) selectedTableInput.value = currentSelectedTable;  
        
        updateGuestsByTable(parseInt(currentSelectedTableNumber));
    } else {
        if (tableSelectionButtons) tableSelectionButtons.style.display = 'flex';
        if (selectedTableDisplay) selectedTableDisplay.style.display = 'none';
        if (selectedTableInput) selectedTableInput.value = '';
    }
}
    if (phoneInput) {
        phoneInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.startsWith('8')) {
                value = '7' + value.substring(1);
            }
            if (!value.startsWith('7')) {
                value = '7' + value;
            }
            value = value.substring(0, 11);
            let formatted = '+7';
            if (value.length > 1) {
                formatted += ' (' + value.substring(1, 4);
            }
            if (value.length >= 5) {
                formatted += ') ' + value.substring(4, 7);
            }
            if (value.length >= 8) {
                formatted += '-' + value.substring(7, 9);
            }
            if (value.length >= 10) {
                formatted += '-' + value.substring(9, 11);
            }
            e.target.value = formatted;
        });
    }


    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
        const maxDate = new Date();
        maxDate.setDate(maxDate.getDate() + 30);
        dateInput.max = maxDate.toISOString().split('T')[0];
    }


    function openTableModal() {
        if (!dateInput.value || !timeInput.value) {
            showPopup('Внимание', 'Сначала выберите дату и время', 'warning');
            return;
        }
        tableModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        loadAvailableTables();
    }

    function closeTableModal() {
        tableModal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    if (openModalBtn) openModalBtn.addEventListener('click', openTableModal);
    if (closeModalBtn) closeModalBtn.addEventListener('click', closeTableModal);

    if (tableModal) {
        tableModal.addEventListener('click', function (e) {
            if (e.target === tableModal) {
                closeTableModal();
            }
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeTableModal();
            closePopup();
        }
    });


    async function loadAvailableTables() {
        const date = dateInput.value;
        const time = timeInput.value;
        const guests = document.getElementById('guests').value || 1;

        const tablesContainer = document.getElementById('tablesContainer');
        const barContainer = document.getElementById('barStoolsContainer');
        
        if (tablesContainer) tablesContainer.innerHTML = '<div class="loading-tables">Загрузка столиков...</div>';
        if (barContainer) barContainer.innerHTML = '<div class="loading-tables">Загрузка...</div>';

        try {
            const response = await fetch(`get_available_tables.php?date=${date}&time=${time}&guests=${guests}`);
            const data = await response.json();

            if (!data.success) {
                if (tablesContainer) tablesContainer.innerHTML = '<div class="error-tables">Ошибка загрузки</div>';
                return;
            }

            const regularTables = data.tables.filter(t => t.capacity > 1);
            const barTables = data.tables.filter(t => t.capacity === 1);

            renderRegularTables(regularTables, data.booked, data.bookedChairs || []);
            renderBarStools(barTables, data.booked);

        } catch (error) {
            console.error(error);
            if (tablesContainer) tablesContainer.innerHTML = '<div class="error-tables">Ошибка загрузки столиков</div>';
            if (barContainer) barContainer.innerHTML = '<div class="error-tables">Ошибка загрузки</div>';
        }
    }

    function renderRegularTables(tables, bookedTables, bookedChairs) {
        const container = document.getElementById('tablesContainer');
        
        if (tables.length === 0) {
            if (container) container.innerHTML = '<div class="no-tables">Нет доступных столиков</div>';
            return;
        }

        const tablesMap = {};
        tables.forEach(table => {
            tablesMap[table.table_number] = table;
        });

        let html = '';
        
        for (let i = 1; i <= 9; i++) {
            if (tablesMap[i]) {
                html += renderTableWithChairs(tablesMap[i], bookedTables, bookedChairs);
            }
        }
        
        if (container) container.innerHTML = html;
        addEventListeners();
    }

    function renderTableWithChairs(table, bookedTables, bookedChairs) {
        const tableNum = table.table_number;
        const config = chairsConfig[tableNum] || { top: 2, left: 0, right: 1, bottom: 2 };
        const isTableBooked = bookedTables.includes(table.id);
        const tableStatusClass = isTableBooked ? 'busy' : 'free';

        let topChairsHtml = '';
        for (let i = 0; i < config.top; i++) {
            const chairId = `${table.id}_chair_top_${i}`;
            const isBooked = bookedChairs.includes(chairId);
            const chairClass = isBooked ? 'busy' : 'free';
            topChairsHtml += `<div class="chair-top ${chairClass}" data-chair-id="${chairId}" data-table-id="${table.id}" data-chair-type="top"></div>`;
        }

        let leftChairHtml = '';
        if (config.left > 0) {
            const chairId = `${table.id}_chair_left`;
            const isBooked = bookedChairs.includes(chairId);
            const chairClass = isBooked ? 'busy' : 'free';
            leftChairHtml = `<div class="chair-left ${chairClass}" data-chair-id="${chairId}" data-table-id="${table.id}" data-chair-type="left"></div>`;
        }

        let rightChairHtml = '';
        if (config.right > 0) {
            const chairId = `${table.id}_chair_right`;
            const isBooked = bookedChairs.includes(chairId);
            const chairClass = isBooked ? 'busy' : 'free';
            rightChairHtml = `<div class="chair-right ${chairClass}" data-chair-id="${chairId}" data-table-id="${table.id}" data-chair-type="right"></div>`;
        }

        let bottomChairsHtml = '';
        for (let i = 0; i < config.bottom; i++) {
            const chairId = `${table.id}_chair_bottom_${i}`;
            const isBooked = bookedChairs.includes(chairId);
            const chairClass = isBooked ? 'busy' : 'free';
            bottomChairsHtml += `<div class="chair-bottom ${chairClass}" data-chair-id="${chairId}" data-table-id="${table.id}" data-chair-type="bottom"></div>`;
        }

        return `
            <div class="table-3d table-${tableNum}">
                <div class="chairs-top">${topChairsHtml}</div>
                <div class="table ${tableStatusClass}" data-table-id="${table.id}" data-table-number="${table.table_number}" data-capacity="${table.capacity}">
                    <div class="table-status-dot"></div>
                </div>
                <div class="chairs-middle">${leftChairHtml}${rightChairHtml}</div>
                <div class="chairs-bottom">${bottomChairsHtml}</div>
            </div>
        `;
    }

    function addEventListeners() {
        document.querySelectorAll('.table').forEach(table => {
            table.addEventListener('click', function(e) {
                e.stopPropagation();
                if (this.classList.contains('busy')) return;
                
                document.querySelectorAll('.table').forEach(t => t.classList.remove('selected'));
                document.querySelectorAll('.chair-top, .chair-left, .chair-right, .chair-bottom').forEach(c => c.classList.remove('selected'));
                document.querySelectorAll('.bar-stool').forEach(b => b.classList.remove('selected'));
                
                this.classList.add('selected');
                currentSelectedTable = this.dataset.tableId;
                currentSelectedTableNumber = this.dataset.tableNumber;
                currentSelectedChair = null;
                currentSelectedChairType = null;
            });
        });
        
        document.querySelectorAll('.chair-top, .chair-left, .chair-right, .chair-bottom').forEach(chair => {
            chair.addEventListener('click', function(e) {
                e.stopPropagation();
                if (this.classList.contains('busy')) return;
                
                document.querySelectorAll('.table').forEach(t => t.classList.remove('selected'));
                document.querySelectorAll('.chair-top, .chair-left, .chair-right, .chair-bottom').forEach(c => c.classList.remove('selected'));
                document.querySelectorAll('.bar-stool').forEach(b => b.classList.remove('selected'));
                
                this.classList.add('selected');
                currentSelectedTable = this.dataset.tableId;
                currentSelectedTableNumber = this.dataset.tableNumber;
                currentSelectedChair = this.dataset.chairId;
                currentSelectedChairType = this.dataset.chairType;
            });
        });
    }

    function renderBarStools(barTables, bookedIds) {
        const container = document.getElementById('barStoolsContainer');
        
        if (barTables.length === 0) {
            if (container) container.innerHTML = '<div class="no-tables">Нет свободных мест у барной стойки</div>';
            return;
        }

        let html = '<div class="bar-stools">';
        
        barTables.forEach(stool => {
            const isBooked = bookedIds.includes(stool.id);
            const statusClass = isBooked ? 'busy' : 'free';
            html += `
                <div class="bar-stool ${statusClass}" data-table-id="${stool.id}" data-table-number="${stool.table_number}">
                    <div class="bar-stool-status-dot"></div>
                </div>
            `;
        });
        
        html += '</div>';
        if (container) container.innerHTML = html;

        document.querySelectorAll('.bar-stool').forEach(stool => {
            stool.addEventListener('click', function() {
                if (this.classList.contains('busy')) return;
                
                document.querySelectorAll('.table').forEach(t => t.classList.remove('selected'));
                document.querySelectorAll('.chair-top, .chair-left, .chair-right, .chair-bottom').forEach(c => c.classList.remove('selected'));
                document.querySelectorAll('.bar-stool').forEach(b => b.classList.remove('selected'));
                
                this.classList.add('selected');
                currentSelectedTable = this.dataset.tableId;
                currentSelectedTableNumber = this.dataset.tableNumber;
                currentSelectedChair = null;
                currentSelectedChairType = null;
                
                updateGuestsByTable(parseInt(currentSelectedTableNumber));
            });
        });
    }


    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (!currentSelectedTable) {
                showPopup('Внимание', 'Выберите столик или место у барной стойки', 'warning');
                return;
            }

            updateSelectedTableDisplay();
            closeTableModal();
        });
    }

    if (changeTableBtn) {
        changeTableBtn.addEventListener('click', function() {
            openTableModal();
        });
    }


    if (clearTableBtn) {
        clearTableBtn.addEventListener('click', function() {
            currentSelectedTable = null;
            currentSelectedTableNumber = null;
            currentSelectedChair = null;
            currentSelectedChairType = null;
            updateSelectedTableDisplay();
            
            showPopup('Готово', 'Выбор столика очищен', 'success');
        });
    }


    if (anyTableBtn) {
        anyTableBtn.addEventListener('click', function () {
            currentSelectedTable = null;
            currentSelectedTableNumber = null;
            currentSelectedChair = null;
            currentSelectedChairType = null;
            updateSelectedTableDisplay();
            showPopup('Готово', 'Будет выбран любой свободный столик', 'success');
        });
    }


    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Отправка...';
            }

            const formData = new FormData(form);
            if (currentSelectedChair) {
                formData.append('selected_chair', currentSelectedChair);
            }

            try {
                const response = await fetch('../process_booking.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    showPopup('Успешно!', result.message, 'success');
                    form.reset();
                    currentSelectedTable = null;
                    currentSelectedTableNumber = null;
                    currentSelectedChair = null;
                    currentSelectedChairType = null;
                    updateSelectedTableDisplay();
                } else {
                    showPopup('Ошибка', result.message, 'error');
                }
            } catch (error) {
                console.error(error);
                showPopup('Ошибка', 'Ошибка соединения', 'error');
            }

            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Забронировать';
            }
        });
    }

});


function showPopup(title, message, type = 'success') {
    const popup = document.getElementById('booking-popup');
    const popupTitle = document.getElementById('popup-title');
    const popupMessage = document.getElementById('popup-message');

    if (!popup || !popupTitle || !popupMessage) return;

    popupTitle.textContent = title;
    popupMessage.textContent = message;

    popup.classList.remove('success', 'error', 'warning');
    popup.classList.add(type);
    popup.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closePopup() {
    const popup = document.getElementById('booking-popup');
    if (popup) {
        popup.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

document.addEventListener('click', function(e) {
    const popup = document.getElementById('booking-popup');
    if (popup && !popup.classList.contains('hidden') && e.target === popup) {
        closePopup();
    }
});