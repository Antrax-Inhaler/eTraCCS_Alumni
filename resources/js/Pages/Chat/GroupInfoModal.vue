<template>
  <div class="modal-overlay">
    <div class="modal-container">
      <div class="modal-header">
        <h3>Group Info</h3>
        <button @click="$emit('close')" class="close-button">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <div class="modal-body">
        <div class="group-avatar-container">
          <img :src="conversation.avatar || '/images/default-group.png'" :alt="conversation.name" class="group-avatar">
        </div>
        
        <div class="group-name">
          {{ conversation.name }}
        </div>
        
        <div class="section-title">Participants ({{ conversation.participants.length }})</div>
        
        <div class="participant-list">
          <div v-for="participant in conversation.participants" :key="participant.id" class="participant-item">
            <img :src="participant.avatar" :alt="participant.name" class="participant-avatar">
            <div class="participant-name">{{ participant.name }}</div>
            <div v-if="participant.id === conversation.created_by" class="admin-badge">Admin</div>
            <div v-if="participant.is_online" class="online-indicator"></div>
          </div>
        </div>
      </div>
      
      <div class="modal-footer">
        <button @click="$emit('add-members')" class="add-members-button">
          <i class="fas fa-user-plus"></i> Add Members
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  conversation: Object
});
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-container {
  background-color: #fff;
  border-radius: 8px;
  width: 100%;
  max-width: 400px;
  max-height: 80vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.modal-header {
  padding: 16px;
  border-bottom: 1px solid #e0e0e0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
  font-size: 18px;
}

.close-button {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 18px;
  color: #616161;
}

.modal-body {
  flex: 1;
  padding: 16px;
  overflow-y: auto;
  text-align: center;
}

.group-avatar-container {
  margin-bottom: 16px;
}

.group-avatar {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
  margin: 0 auto;
}

.group-name {
  font-size: 20px;
  font-weight: 500;
  margin-bottom: 24px;
}

.section-title {
  text-align: left;
  font-weight: 500;
  margin-bottom: 12px;
  color: #757575;
  border-bottom: 1px solid #e0e0e0;
  padding-bottom: 8px;
}

.participant-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.participant-item {
  display: flex;
  align-items: center;
  gap: 12px;
  position: relative;
}

.participant-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.participant-name {
  flex: 1;
  text-align: left;
}

.admin-badge {
  background-color: #e3f2fd;
  color: #1976d2;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 12px;
}

.online-indicator {
  width: 10px;
  height: 10px;
  background-color: #4caf50;
  border-radius: 50%;
  position: absolute;
  bottom: 0;
  left: 30px;
  border: 2px solid #fff;
}

.modal-footer {
  padding: 12px 16px;
  border-top: 1px solid #e0e0e0;
  display: flex;
  justify-content: center;
}

.add-members-button {
  background-color: #3f51b5;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.add-members-button:hover {
  background-color: #303f9f;
}
</style>