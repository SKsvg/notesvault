document.addEventListener("DOMContentLoaded", function () {

    // ------------------------------------------------------------------
    // --- PROFILE MODAL LOGIC & VARIABLES ---
    // ------------------------------------------------------------------

    const editBtn = document.querySelector(".edit-profile-btn");
    const editModal = document.getElementById("editModal");
    const closeModalBtn = document.getElementById("closeModal");
    const cancelBtn = document.getElementById("cancelBtn");
    const editForm = document.getElementById("editForm");

    const profilePicInput = document.getElementById("profilePic");
    const avatarPreview = document.getElementById("avatarPreview");
    const profileNameEl = document.querySelector(".profile-card h2");
    const profileEmailEl = document.querySelector(".profile-card .email");
    
    // NOTE: The PHP side of dashboard.php must be updated to include the 'phone' detail-item for this selector to work.
    // Assuming the HTML structure from previous versions:
    // This is NOT used in the modal logic below but keeping for reference if you add it to HTML.
    // const profilePhoneEl = document.querySelector(".profile-card .phone"); 
    
    const profileInstitutionEl = document.querySelector(".profile-card .institution");
    const profileBranchEl = document.querySelector(".profile-card .detail-item:nth-child(1) p");
    const profileYearEl = document.querySelector(".profile-card .detail-item:nth-child(2) p");
    const profileStudentIDEl = document.querySelector(".profile-card .detail-item:nth-child(3) p");
    const profileAvatarWrapper = document.querySelector(".profile-card .avatar");

    function openModal() {
        if (!editModal) return;
        editModal.classList.add("active");
        editModal.setAttribute("aria-hidden", "false");
        document.body.style.overflow = "hidden";
    }

    function closeEditModal() {
        if (!editModal) return;
        editModal.classList.remove("active");
        editModal.setAttribute("aria-hidden", "true");
        document.body.style.overflow = "";
    }

    // Profile Modal Listeners (Existing)
    if (editBtn) {
        editBtn.addEventListener("click", () => {
            if (profileNameEl) document.getElementById("name").value = profileNameEl.textContent.trim();
            if (profileEmailEl) document.getElementById("email").value = profileEmailEl.textContent.trim();
            
            // FIX/NOTE: Your PHP dashboard.php does not show a dedicated element for phone
            // If you added it to HTML, it would look like this:
            // if (profilePhoneEl) document.getElementById("phone").value = profilePhoneEl.textContent.trim();

            if (profileInstitutionEl) document.getElementById("institution").value = profileInstitutionEl.textContent.trim();
            if (profileBranchEl) document.getElementById("branch").value = profileBranchEl.textContent.trim();
            if (profileYearEl) document.getElementById("year").value = profileYearEl.textContent.trim();
            if (profileStudentIDEl) document.getElementById("student_ID").value = profileStudentIDEl.textContent.trim();

            const existingImg = profileAvatarWrapper.querySelector("img");
            if (existingImg) {
                avatarPreview.src = existingImg.src;
            } else {
                avatarPreview.src = "../assets/index/images/default-avatar.png"; 
            }
            openModal();
        });
    }

    if (closeModalBtn) closeModalBtn.addEventListener("click", closeEditModal);
    if (cancelBtn) cancelBtn.addEventListener("click", closeEditModal);
    if (editModal) {
        editModal.addEventListener("click", (e) => {
            if (e.target === editModal) closeEditModal();
        });
    }

    // Avatar change functionality (Existing)
    if (profilePicInput) {
        profilePicInput.addEventListener("change", (e) => {
            const file = e.target.files && e.target.files[0];
            if (!file) return;
            if (!file.type.startsWith("image/")) {
                alert("Please choose an image file (jpg, png, etc.)");
                return;
            }
            const reader = new FileReader();
            reader.onload = () => {
                avatarPreview.src = reader.result;
                const existingImg = profileAvatarWrapper.querySelector("img");
                if (existingImg) {
                    existingImg.src = reader.result;
                } else {
                    profileAvatarWrapper.innerHTML = "";
                    const img = document.createElement("img");
                    img.src = reader.result;
                    img.alt = "Profile avatar";
                    img.style.width = "100%";
                    img.style.height = "100%";
                    img.style.objectFit = "cover";
                    profileAvatarWrapper.appendChild(img);
                }
            };
            reader.readAsDataURL(file);
        });
    }

    // ⛔️ CRITICAL FIX: The previous code was preventing the form submission.
    // We remove the preventDefault() and the JS DOM manipulation.
    if (editForm) {
        editForm.addEventListener("submit", (e) => {
            // Remove e.preventDefault();
            
            // The profile saving is now handled by the PHP script (save_profile.php) 
            // set in the form's 'action' attribute. 
            // We just let the form submit normally.
            
            // The alert and closeModal are also removed, as the page will redirect
            // after the PHP script successfully saves the data.
            // alert("Profile updated successfully!"); // REMOVED
            // closeEditModal(); // REMOVED
        });
    }

    // ------------------------------------------------------------------
    // --- CALENDAR & EVENT MANAGEMENT LOGIC (No changes needed) ---
    // ------------------------------------------------------------------

    const calendarDates = document.getElementById('calendarDates');
    const currentMonthYear = document.getElementById('currentMonthYear');
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');

    let date = new Date();
    date.setDate(1); 

    // --- EVENT DATA ---
    let allEvents = {};
    function initializeEvents() {
        allEvents['2025-10-10'] = [{ title: 'Assignment Due - Database Systems', type: 'Assignment' }];
        allEvents['2025-10-15'] = [{ title: 'Midterm Exam - Calculus II', type: 'Exam' }];
        allEvents['2025-10-28'] = [{ title: 'Group Project Meeting', type: 'Meeting' }];
        allEvents['2025-11-5'] = [{ title: 'Project Checkpoint - Software Engineering', type: 'Project' }];
    }
    initializeEvents();

    // ------------------------------------------------------------------
    // --- EVENT ADD/DETAIL MODAL VARIABLES & FUNCTIONS ---
    // ------------------------------------------------------------------

    const addEventBtn = document.getElementById('addEventBtn');
    const addEventModal = document.getElementById('addEventModal');
    const closeEventModalBtn = document.getElementById('closeEventModal');
    const eventForm = document.getElementById('eventForm');
    const eventDateInput = document.getElementById('eventDate');
    
    // DETAIL MODAL VARIABLES (NEW)
    const eventDetailModal = document.getElementById('eventDetailModal');
    const closeDetailModalBtn = document.getElementById('closeDetailModal');
    const detailDateSpan = document.getElementById('detailDate');
    const eventListContainer = document.getElementById('eventListContainer');
    let addNewTaskForDayBtn = document.getElementById('addNewTaskForDayBtn'); 

    // GLOBAL STATE VARIABLES FOR EDITING
    let currentSelectedDateKey = null; 
    let isEditMode = false;
    let editingEventKey = null; 
    let editingEventIndex = null; 

    // Add Event Modal Functions
    function openEventModal() {
        if (!addEventModal) return;
        addEventModal.classList.add("active");
        addEventModal.setAttribute("aria-hidden", "false");
        document.body.style.overflow = "hidden";
    }

    function closeEventModal() {
        if (!addEventModal) return;
        addEventModal.classList.remove("active");
        addEventModal.setAttribute("aria-hidden", "true");
        document.body.style.overflow = "";
        eventForm.reset(); 
        
        // ** RESET LOGIC FOR EDIT MODE **
        isEditMode = false;
        editingEventKey = null;
        editingEventIndex = null;
        
        // Reset modal header/button text to default "Add" mode
        if (document.getElementById('addEventTitle')) {
            document.getElementById('addEventTitle').textContent = 'Add New Task/Event'; // Changed to match your PHP file
        }
        if (document.querySelector('#eventForm .save-btn')) { // Selecting the correct button in the form
            document.querySelector('#eventForm .save-btn').textContent = 'Save Task';
        }
    }
    
    // Detail Modal Functions (NEW)
    function closeDetailModal() {
        if (!eventDetailModal) return;
        eventDetailModal.classList.remove("active");
        eventDetailModal.setAttribute("aria-hidden", "true");
        document.body.style.overflow = "";
    }

    function openAddEventModalWithDate() {
        closeDetailModal(); 
        // Reset the form mode to ensure it's "Add" when opening from the "Add New Task" button
        closeEventModal();
        
        if (eventDateInput && currentSelectedDateKey) {
            // Convert YYYY-M-D key back to YYYY-MM-DD format for the date input field
            const [y, m, d] = currentSelectedDateKey.split('-');
            const paddedMonth = m.padStart(2, '0');
            const paddedDay = d.padStart(2, '0');
            eventDateInput.value = `${y}-${paddedMonth}-${paddedDay}`;
        }
        openEventModal();
    }
    
    function handleDeleteTask(dateKey, index) {
        if (confirm("Are you sure you want to delete this task?")) {
            allEvents[dateKey].splice(index, 1); 
            
            if (allEvents[dateKey].length === 0) {
                delete allEvents[dateKey];
            }

            closeDetailModal();
            alert("Task deleted successfully!");
            renderCalendar(); 
        }
    }
    
    // ** UPDATED: Populates form and switches to edit mode **
    function handleEditTask(dateKey, index) {
        const eventToEdit = allEvents[dateKey][index];
        
        // 1. Set global state for editing
        isEditMode = true;
        editingEventKey = dateKey;
        editingEventIndex = index;

        // 2. Format the date key (YYYY-M-D) to input format (YYYY-MM-DD)
        const [y, m, d] = dateKey.split('-');
        const paddedMonth = m.padStart(2, '0');
        const paddedDay = d.padStart(2, '0');

        // 3. Populate the form fields in the Add Event Modal
        if (document.getElementById('eventTitle')) document.getElementById('eventTitle').value = eventToEdit.title;
        if (document.getElementById('eventDate')) document.getElementById('eventDate').value = `${y}-${paddedMonth}-${paddedDay}`;
        if (document.getElementById('eventType')) document.getElementById('eventType').value = eventToEdit.type;

        // 4. Update the modal title and button text
        if (document.querySelector('#addEventModal h3')) document.querySelector('#addEventModal h3').textContent = 'Edit Task';
        if (document.querySelector('#eventForm .save-btn')) document.querySelector('#eventForm .save-btn').textContent = 'Save Changes';

        // 5. Close the detail modal and open the main add/edit modal
        closeDetailModal();
        openEventModal();
    }

    // Function to open the detail modal and populate event list
    function showEventDetails(dateKey) {
        if (!eventDetailModal) return;

        currentSelectedDateKey = dateKey;

        const dayEvents = allEvents[dateKey] || [];
        const dateParts = dateKey.split('-');
        const formattedDate = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]).toLocaleDateString('en-US', {
            weekday: 'short', month: 'short', day: 'numeric', year: 'numeric'
        });

        detailDateSpan.textContent = formattedDate;
        eventListContainer.innerHTML = ''; 

        if (dayEvents.length === 0) {
            eventListContainer.innerHTML = '<p style="text-align: center; color: var(--text-color-secondary); padding: 10px 0;">No tasks scheduled for this day.</p>';
        } else {
            dayEvents.forEach((event, index) => {
                const item = document.createElement('div');
                item.className = 'event-item';
                item.innerHTML = `
                    <div class="event-item-info">
                        <span class="event-item-title">${event.title}</span>
                        <span class="event-item-type">${event.type}</span>
                    </div>
                    <div class="event-actions">
                        <button class="edit-btn" data-index="${index}"><i class="fas fa-edit"></i></button>
                        <button class="delete-btn" data-index="${index}"><i class="fas fa-trash-alt"></i></button>
                    </div>
                `;
                eventListContainer.appendChild(item);
            });

            // Add listeners for the new Edit/Delete buttons
            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', (e) => handleEditTask(dateKey, parseInt(e.currentTarget.dataset.index)));
            });
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', (e) => handleDeleteTask(dateKey, parseInt(e.currentTarget.dataset.index)));
            });
        }

        // FIX: Update the global reference to the new button after cloning
        if (addNewTaskForDayBtn) {
            const oldBtn = addNewTaskForDayBtn;
            const newBtn = oldBtn.cloneNode(true); 
            
            if (oldBtn.parentNode) {
                oldBtn.parentNode.replaceChild(newBtn, oldBtn);
            }
            
            // Update the global reference!
            addNewTaskForDayBtn = newBtn;
            
            // Add the listener to the new element
            addNewTaskForDayBtn.addEventListener('click', openAddEventModalWithDate);
        }
        
        eventDetailModal.classList.add("active");
        eventDetailModal.setAttribute("aria-hidden", "false");
        document.body.style.overflow = "hidden";
    }

    // ------------------------------------------------------------------
    // --- CALENDAR RENDERING ---
    // ------------------------------------------------------------------

    function renderCalendar() {
        if (!calendarDates) return;

        const year = date.getFullYear();
        const month = date.getMonth(); 
        const today = new Date();

        const options = { year: 'numeric', month: 'long' };
        currentMonthYear.innerHTML = `<i class="fas fa-calendar-alt"></i> ${date.toLocaleDateString('en-US', options)}`;

        const firstDayOfMonth = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const daysInPrevMonth = new Date(year, month, 0).getDate();

        let cells = '';

        // A. Previous month padding
        for (let i = 0; i < firstDayOfMonth; i++) {
            const day = daysInPrevMonth - firstDayOfMonth + i + 1;
            cells += `<div class="date-cell current-month-false">${day}</div>`;
        }

        // B. Current month dates
        for (let day = 1; day <= daysInMonth; day++) {
            let cellClass = 'date-cell';
            const fullDateString = `${year}-${month + 1}-${day}`; 
            const dayEvents = allEvents[fullDateString];

            if (day === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
                cellClass += ' today';
            }

            if (dayEvents && dayEvents.length > 0) {
                cellClass += ' has-event';
            }
            cells += `<div class="${cellClass}" data-date="${fullDateString}">${day}</div>`;
        }

        // C. Next month padding
        const totalCells = firstDayOfMonth + daysInMonth;
        const remainingCells = 42 - totalCells; 

        for (let i = 1; i <= remainingCells; i++) {
            if (totalCells + (i - 1) < 42) {
                cells += `<div class="date-cell current-month-false">${i}</div>`;
            }
        }

        calendarDates.innerHTML = cells;

        // Use showEventDetails on click
        document.querySelectorAll('.date-cell:not(.current-month-false)').forEach(cell => {
            cell.addEventListener('click', (e) => {
                document.querySelectorAll('.selected-date').forEach(el => el.classList.remove('selected-date'));
                e.currentTarget.classList.add('selected-date');
                const selectedDateKey = e.currentTarget.getAttribute('data-date');
                showEventDetails(selectedDateKey);
            });
        });
    }

    // Navigation Buttons (Existing)
    if (prevMonthBtn) {
        prevMonthBtn.addEventListener('click', () => {
            date.setMonth(date.getMonth() - 1);
            renderCalendar();
        });
    }

    if (nextMonthBtn) {
        nextMonthBtn.addEventListener('click', () => {
            date.setMonth(date.getMonth() + 1);
            renderCalendar();
        });
    }

    // Initial calendar render
    renderCalendar();

    // ------------------------------------------------------------------
    // --- EVENT MODAL LISTENERS (CONT.) ---
    // ------------------------------------------------------------------

    // 1. Open Add Event Modal Listener (Resets to Add Mode)
    if (addEventBtn) {
        addEventBtn.addEventListener('click', () => {
            closeEventModal(); // Ensure reset to 'Add Mode'
            const todayString = new Date().toISOString().substring(0, 10);
            if (eventDateInput) eventDateInput.value = todayString;
            openEventModal();
        });
    }

    // 2. Close Add Event Modal Listeners
    if (closeEventModalBtn) closeEventModalBtn.addEventListener('click', closeEventModal);
    if (addEventModal) {
        addEventModal.addEventListener('click', (e) => {
            if (e.target === addEventModal) closeEventModal();
        });
    }
    
    // 3. Form Submission Handler (UPDATED FOR EDIT/ADD MODE)
    if (eventForm) {
        eventForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const title = document.getElementById('eventTitle').value.trim();
            const dateStr = document.getElementById('eventDate').value;
            const type = document.getElementById('eventType').value;
            
            if (!title || !dateStr || !type) {
                alert("Please fill in all event details.");
                return;
            }

            // Convert date to YYYY-M-D format for storage key
            const dateParts = dateStr.split('-'); 
            const year = dateParts[0];
            const month = parseInt(dateParts[1], 10); 
            const day = parseInt(dateParts[2], 10);
            const storageKey = `${year}-${month}-${day}`;

            const newEventData = { title, type };

            if (isEditMode) {
                // --- EDIT MODE LOGIC ---
                if (storageKey !== editingEventKey) {
                    // Date has changed: Remove old, add new
                    allEvents[editingEventKey].splice(editingEventIndex, 1);
                    
                    if (allEvents[editingEventKey].length === 0) {
                        delete allEvents[editingEventKey];
                    }
                    
                    if (!allEvents[storageKey]) {
                        allEvents[storageKey] = [];
                    }
                    allEvents[storageKey].push(newEventData);
                    
                } else {
                    // Date is the same: Just replace the data
                    allEvents[editingEventKey][editingEventIndex] = newEventData;
                }

                alert(`Task "${title}" updated successfully.`);
                
            } else {
                // --- ADD MODE LOGIC ---
                if (!allEvents[storageKey]) {
                    allEvents[storageKey] = [];
                }
                allEvents[storageKey].push(newEventData);
                alert(`Event "${title}" saved for ${dateStr}.`);
            }

            closeEventModal();
            
            // Navigate/re-render to show correct dots for the month
            date = new Date(year, month - 1, 1); 
            renderCalendar();
        });
    }

    // 4. Detail Modal Close Listeners (NEW)
    if (closeDetailModalBtn) {
        closeDetailModalBtn.addEventListener('click', closeDetailModal);
    }
    if (eventDetailModal) {
        eventDetailModal.addEventListener('click', (e) => {
            if (e.target === eventDetailModal) closeDetailModal();
        });
    }

});