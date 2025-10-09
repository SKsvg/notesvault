<?php
// THIS IS THE LINE YOU NEED TO ADD AT THE VERY TOP
require_once '../pages/session_check.php'; // Make sure this path is correct!
?>

<?php

if (!isset($_SESSION['user_id'])) {
    // Redirect them to the login page
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>NotesVault - Study Groups</title>

    <!-- Favicon -->
    <link
      rel="icon"
      href="../assets/index/images/favicon.png"
      type="image/x-icon"
    />

    <!-- Font Awesome -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    />

    <!-- CSS -->
    <link rel="stylesheet" href="../styling/studygroup.css" />
    <link rel="stylesheet" href="../styling/group-card.css" />
    <link rel="stylesheet" href="../styling/base.css" />
    <link rel="stylesheet" href="../styling/variables.css" />

    <!-- Minimal styles for the modal (if your CSS already covers it you can remove this) -->
    <style>
      /* Basic modal styles - safe fallback */
      .modal {
        position: fixed;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0,0,0,0.45);
        opacity: 0;
        pointer-events: none;
        transition: opacity 160ms ease-in-out;
        z-index: 1200;
      }
      .modal.active { opacity: 1; pointer-events: auto; }
      .modal .modal-content {
        position: relative;
        background: var(--card-bg, #fff);
        border-radius: 10px;
        width: 92%;
        max-width: 480px;
        padding: 20px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
      }
      .modal .modal-header { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:12px; }
      .modal .modal-footer { display:flex; justify-content:flex-end; gap:8px; margin-top:12px; }
      .close-modal {
        border: none;
        background: transparent;
        font-size: 1.15rem;
        cursor: pointer;
      }
      .modal input[type="text"], .modal input[type="email"] {
        width: 100%;
        padding: 8px 10px;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        font-size: 0.98rem;
      }
      .btn {
        background: #90ee90;
        color: #222;
        border: none;
        border-radius: 6px;
        padding: 8px 14px;
        font-size: 0.97rem;
        cursor: pointer;
      }
      .btn.secondary {
        background: #e5e7eb;
        color: #111;
      }
    </style>
  </head>

  <body>
    <!-- Header -->
    <?php include '../components/header.php'; ?>

    <!-- Hero Section -->
    <section class="studygroup-hero">
      <div class="container">
        <h1>Study Groups</h1>
        <p class="subtitle">
          Make a group with friends, share notes, chat, schedule meetings, and
          play subject-based games—all in one collaborative space.
        </p>
      </div>
    </section>

    <!-- Main Section -->
    <main class="studygroup-container">
      <div class="group-actions">
        <button id="createGroupBtn" class="btn">
          <i class="fas fa-users"></i> Create Group
        </button>

        <!-- Join Group -->
        <input
          type="text"
          id="joinGroupInput"
          placeholder="Enter group name to join"
          style="padding:7px 10px; border-radius:5px; border:1px solid #cbd5e1; font-size:0.97rem; margin-left:10px;"
        />
        <button id="joinGroupBtn" class="btn" style="margin-left:8px;">
          Join Group
        </button>
      </div>

      <!-- Groups List -->
      <div class="groups-list" id="groupsList">
        <!-- Groups will be listed here -->
      </div>
    </main>

    <!-- Create Group Modal -->
    <div id="createGroupModal" class="modal" aria-hidden="true">
      <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="createGroupTitle">
        <div class="modal-header">
          <h3 id="createGroupTitle" style="margin:0; font-size:1.1rem;">Create a new group</h3>
          <button id="closeGroupModal" class="close-modal" title="Close">&times;</button>
        </div>

        <div class="modal-body">
          <label for="groupNameInput" style="display:block; margin-bottom:6px; font-size:0.95rem;">Group name</label>
          <input id="groupNameInput" type="text" placeholder="e.g. Algorithms Study Group" />
          <div id="groupCreateMsg" style="margin-top:8px; font-size:0.95rem;"></div>
        </div>

        <div class="modal-footer">
          <button id="cancelGroupBtn" class="btn secondary">Cancel</button>
          <button id="submitGroupBtn" class="btn">Create</button>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <?php include '../components/footer.php'; ?>

    <!-- JavaScript -->
    <script src="../scripts/header.js" defer></script>
    <script src="../scripts/script.js" defer></script>

    <script>
      // Current logged-in user's email from PHP (escaped safely)
      const currentUser = "<?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email'], ENT_QUOTES) : 'demo@user.com'; ?>";

      // Local cache for groups so UI can be updated optimistically
      let cachedGroups = [];

      // Utility: safe HTML escape for rendering text nodes
      function escapeHtml(str) {
        if (typeof str !== "string") return "";
        return str
          .replace(/&/g, "&amp;")
          .replace(/</g, "&lt;")
          .replace(/>/g, "&gt;")
          .replace(/"/g, "&quot;")
          .replace(/'/g, "&#039;");
      }

      // Render groups into the page (cachedGroups kept as plain objects)
      function renderGroups(groups) {
        cachedGroups = Array.isArray(groups) ? groups.slice() : [];
        const userEmail = currentUser;
        let html = "";
        if (!cachedGroups || cachedGroups.length === 0) {
          html = "<p>No groups created yet.</p>";
        } else {
          cachedGroups.forEach((group) => {
            const isMember = Array.isArray(group.members) && group.members.includes(userEmail);
            const isCreator = group.creator === userEmail;

            const leaveBtn =
              isMember && !isCreator
                ? `<button class="leaveBtn btn" data-group="${encodeURIComponent(group.name)}" style="margin-top:10px;">Leave Group</button>`
                : "";

            const goInsideBtn =
              isMember
                ? `<button class="goInsideBtn btn" data-group="${encodeURIComponent(group.name)}" 
                    style="margin-top:10px; margin-right:8px;">
                    Go Inside</button>`
                : "";

            const deleteBtn =
              isCreator
                ? `<button class="deleteBtn btn" data-group="${encodeURIComponent(group.name)}" style="margin-top:10px;">Delete Group</button>`
                : "";

            const addMember =
              isCreator
                ? `<div style="margin-top:10px;">
                    <input type="email" class="addMemberInput" placeholder="Add member by email"
                      style="width:70%; padding:7px 10px; margin-right:8px; border-radius:5px; border:1px solid #cbd5e1; font-size:0.97rem;" />
                    <button class="addMemberBtn btn" data-group="${encodeURIComponent(group.name)}"
                      style="padding:7px 14px; font-size:0.95rem;">
                      Add Member
                    </button>
                    <div class="addMemberMsg" style="color:red; margin-top:5px; font-size:0.95rem;"></div>
                  </div>`
                : "";

            html += `
              <div class="group-card${group.justCreated ? " new-group" : ""}" style="margin-bottom:14px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                  <h4 style="margin:0; color:${group.justCreated ? "#7c3aed" : "#4f46e5"}; font-size:1.15rem;">${escapeHtml(group.name)}</h4>
                  ${group.justCreated ? '<span style="background:#7c3aed; color:#fff; border-radius:4px; padding:2px 8px; font-size:0.92rem;">New</span>' : ""}
                </div>
                <p style="margin:0 0 6px 0; color:#4f46e5; font-size:0.97rem;">Created by: ${escapeHtml(group.creator)}</p>
                <p style="margin:0 0 10px 0; color:#555; font-size:0.96rem;">Members: ${Array.isArray(group.members) ? group.members.length : 0}</p>
                ${addMember}
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                  ${goInsideBtn}
                  ${leaveBtn}
                  ${deleteBtn}
                </div>
              </div>
            `;
          });
        }
        const container = document.getElementById("groupsList");
        if (container) container.innerHTML = html;
      }

      // Fetch groups from server and render (with fallback to cachedGroups)
      function loadGroups() {
        fetch("groups.php?api=get_groups", { cache: "no-store" })
          .then((res) => {
            if (!res.ok) throw new Error("Failed to load groups");
            return res.json();
          })
          .then((groups) => {
            if (!Array.isArray(groups)) groups = [];
            renderGroups(groups);
          })
          .catch((err) => {
            console.warn("Could not fetch groups — using cachedGroups if available", err);
            renderGroups(cachedGroups || []);
          });
      }

      // Find group by name (case-insensitive) in cachedGroups
      function findGroupIndexByName(name) {
        if (!name) return -1;
        return cachedGroups.findIndex(g => g.name && g.name.toLowerCase() === name.toLowerCase());
      }

      // Helper to decode data-group attribute
      function readGroupNameFromAttr(el) {
        const raw = el.getAttribute("data-group") || "";
        try { return decodeURIComponent(raw); } catch (e) { return raw; }
      }

      // Wire up modal and controls (guarded in case elements missing)
      (function modalSetup() {
        const createBtn = document.getElementById("createGroupBtn");
        const modal = document.getElementById("createGroupModal");
        const closeBtn = document.getElementById("closeGroupModal");
        const cancelBtn = document.getElementById("cancelGroupBtn");
        const submitBtn = document.getElementById("submitGroupBtn");
        const nameInput = document.getElementById("groupNameInput");
        const msgDiv = document.getElementById("groupCreateMsg");

        function openModal() {
          if (modal) modal.classList.add("active");
          if (nameInput) { nameInput.value = ""; nameInput.focus(); }
          if (msgDiv) { msgDiv.innerText = ""; msgDiv.style.color = ""; }
        }
        function closeModal() {
          if (modal) modal.classList.remove("active");
          if (msgDiv) { msgDiv.innerText = ""; }
        }

        if (createBtn) createBtn.addEventListener("click", openModal);
        if (closeBtn) closeBtn.addEventListener("click", closeModal);
        if (cancelBtn) cancelBtn.addEventListener("click", closeModal);

        // allow clicking outside modal content to close
        if (modal) {
          modal.addEventListener("click", (ev) => {
            if (ev.target === modal) closeModal();
          });
        }

        // pressing Escape closes modal
        document.addEventListener("keydown", (ev) => {
          if (ev.key === "Escape" && modal && modal.classList.contains("active")) closeModal();
        });

        // Submit group creation
        if (submitBtn) {
          submitBtn.addEventListener("click", () => {
            if (!nameInput) return;
            const groupName = nameInput.value.trim();
            if (!groupName) {
              msgDiv.style.color = "red";
              msgDiv.innerText = "Please enter a group name.";
              return;
            }
            if (findGroupIndexByName(groupName) !== -1) {
              msgDiv.style.color = "red";
              msgDiv.innerText = "A group with that name already exists.";
              return;
            }

            // optimistic message
            msgDiv.style.color = "orange";
            msgDiv.innerText = "Creating...";

            fetch("groups.php", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ groupName: groupName, creator: currentUser }),
            })
            .then(res => res.json().catch(() => ({})))
            .then((data) => {
              if (data && data.success) {
                msgDiv.style.color = "green";
                msgDiv.innerText = data.message || "Group created.";
                // refresh groups
                loadGroups();
                setTimeout(() => {
                  closeModal();
                }, 600);
              } else {
                msgDiv.style.color = "red";
                msgDiv.innerText = (data && data.message) || "Could not create group.";
              }
            })
            .catch((err) => {
              console.error("Create group failed:", err);
              msgDiv.style.color = "red";
              msgDiv.innerText = "Could not create group (network error).";
            });
          });
        }

        // Pressing Enter in the input will submit
        if (nameInput) {
          nameInput.addEventListener("keydown", (ev) => {
            if (ev.key === "Enter") {
              ev.preventDefault();
              if (submitBtn) submitBtn.click();
            }
          });
        }
      })();

      // Delegated handlers: add member, leave, delete, go inside
      document.getElementById("groupsList").addEventListener("click", (e) => {
        const target = e.target;
        const userEmail = currentUser;

        // Go inside
        if (target.classList.contains("goInsideBtn")) {
          const groupName = readGroupNameFromAttr(target);
          if (!groupName) return;
          localStorage.setItem("selectedGroup", groupName);
          window.location.href = "groupdetails.php?group=" + encodeURIComponent(groupName);
          return;
        }

        // Leave group
        if (target.classList.contains("leaveBtn")) {
          const groupName = readGroupNameFromAttr(target);
          if (!groupName) return;
          if (!confirm("Are you sure you want to leave this group?")) return;
          fetch("leaveGroup.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ groupName: groupName, email: userEmail }),
          })
          .then(res => res.json().catch(()=>({})))
          .then(data => {
            // Reload from server to reflect changes
            loadGroups();
            alert((data && data.message) || "Left group.");
          })
          .catch(err => {
            console.error("Leave failed:", err);
            alert("Could not leave group (network error).");
          });
          return;
        }

        // Add member (creator only)
        if (target.classList.contains("addMemberBtn")) {
          const groupName = readGroupNameFromAttr(target);
          if (!groupName) return;
          const container = target.parentElement;
          const emailInput = container ? container.querySelector(".addMemberInput") : null;
          const msgDiv = container ? container.querySelector(".addMemberMsg") : null;
          const email = emailInput ? emailInput.value.trim() : "";
          if (!email) {
            if (msgDiv) { msgDiv.style.color = "red"; msgDiv.innerText = "Enter an email."; }
            return;
          }
          if (msgDiv) { msgDiv.style.color = "orange"; msgDiv.innerText = "Adding..."; }
          fetch("addMember.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ groupName: groupName, email: email }),
          })
          .then(res => res.json().catch(()=>({})))
          .then(data => {
            if (data && data.success) {
              if (msgDiv) { msgDiv.style.color = "green"; msgDiv.innerText = data.message || "Member added."; }
            } else {
              if (msgDiv) { msgDiv.style.color = "red"; msgDiv.innerText = (data && data.message) || "Error adding member."; }
            }
            // Optimistically update local cache
            const idx = findGroupIndexByName(groupName);
            if (idx === -1) {
              loadGroups();
            } else {
              const members = cachedGroups[idx].members || [];
              if (!members.includes(email)) {
                members.push(email);
                cachedGroups[idx].members = members;
              }
              renderGroups(cachedGroups);
            }
            if (emailInput) emailInput.value = "";
          })
          .catch(err => {
            console.error("Add member failed:", err);
            if (msgDiv) { msgDiv.style.color = "red"; msgDiv.innerText = "Could not add member (network)."; }
          });
          return;
        }

        // Delete group (creator only)
        if (target.classList.contains("deleteBtn")) {
          const groupName = readGroupNameFromAttr(target);
          if (!groupName) return;
          if (!confirm("Delete this group? This cannot be undone.")) return;
          fetch("deleteGroup.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ groupName: groupName }),
          })
          .then(res => res.json().catch(()=>({})))
          .then(data => {
            // Remove from cachedGroups anyway (optimistic)
            const idx = findGroupIndexByName(groupName);
            if (idx !== -1) {
              cachedGroups.splice(idx, 1);
              renderGroups(cachedGroups);
            } else {
              loadGroups();
            }
            if (!(data && data.success)) {
              // server didn't confirm — still removed locally (we attempted optimistic UX)
            }
          })
          .catch(err => {
            console.error("Delete failed:", err);
            alert("Could not delete group (network error).");
          });
          return;
        }
      });

      // Join group button
      (function setupJoin() {
        const joinBtn = document.getElementById("joinGroupBtn");
        const joinInput = document.getElementById("joinGroupInput");
        if (!joinBtn || !joinInput) return;
        joinBtn.addEventListener("click", () => {
          const groupCode = joinInput.value.trim();
          const userEmail = currentUser;
          if (!groupCode) {
            alert("Please enter a group name to join.");
            return;
          }

          fetch("joinGroup.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ groupName: groupCode, email: userEmail }),
          })
          .then(res => res.json().catch(() => ({})))
          .then(data => {
            if (data && data.success) {
              loadGroups();
              joinInput.value = "";
              alert(data.message || "Joined group.");
            } else {
              alert((data && data.message) || "Could not join group.");
            }
          })
          .catch(err => {
            console.error("Join failed:", err);
            alert("Could not join group (network error).");
          });
        });

        // allow Enter key to join
        joinInput.addEventListener("keydown", (ev) => {
          if (ev.key === "Enter") {
            ev.preventDefault();
            joinBtn.click();
          }
        });
      })();

      // Page load
      window.addEventListener("load", loadGroups);
    </script>
  </body>
</html>
