<template>
  <div class="reservation-calendar">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <button class="btn btn-sm btn-outline-primary" @click="previousMonth">
        <i class="bi bi-chevron-left"></i> Anterior
      </button>
      <h5 class="mb-0">{{ currentMonthName }} {{ currentYear }}</h5>
      <button class="btn btn-sm btn-outline-primary" @click="nextMonth">
        Próximo <i class="bi bi-chevron-right"></i>
      </button>
    </div>

    <table class="table table-bordered text-center">
      <thead>
        <tr>
          <th>Dom</th>
          <th>Seg</th>
          <th>Ter</th>
          <th>Qua</th>
          <th>Qui</th>
          <th>Sex</th>
          <th>Sáb</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="week in calendar" :key="week[0]?.date">
          <td v-for="day in week" 
              :key="day?.date"
              :class="getDayClass(day)"
              @click="selectDay(day)"
              style="cursor: pointer; height: 80px; vertical-align: top;">
            <div v-if="day">
              <div class="fw-bold">{{ day.day }}</div>
              <small v-if="day.reservations > 0" class="badge bg-warning">
                {{ day.reservations }}
              </small>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Modal de Detalhes do Dia -->
    <div v-if="selectedDay" class="mt-3">
      <div class="alert alert-info">
        <h6>Reservas para {{ selectedDay.date }}</h6>
        <ul v-if="selectedDay.reservationList?.length">
          <li v-for="res in selectedDay.reservationList" :key="res.id">
            {{ res.start_time }} - {{ res.end_time }}: {{ res.space.name }}
          </li>
        </ul>
        <p v-else class="mb-0">Nenhuma reserva neste dia</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ReservationCalendar',
  props: {
    spaceId: {
      type: Number,
      default: null,
    },
  },
  data() {
    return {
      currentMonth: new Date().getMonth(),
      currentYear: new Date().getFullYear(),
      calendar: [],
      reservations: [],
      selectedDay: null,
    };
  },
  computed: {
    currentMonthName() {
      const months = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                     'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
      return months[this.currentMonth];
    },
  },
  mounted() {
    this.generateCalendar();
    this.loadReservations();
  },
  methods: {
    generateCalendar() {
      const firstDay = new Date(this.currentYear, this.currentMonth, 1);
      const lastDay = new Date(this.currentYear, this.currentMonth + 1, 0);
      const startDate = new Date(firstDay);
      startDate.setDate(startDate.getDate() - startDate.getDay());

      const weeks = [];
      let week = [];

      for (let i = 0; i < 42; i++) {
        const date = new Date(startDate);
        date.setDate(startDate.getDate() + i);

        if (date.getMonth() === this.currentMonth) {
          week.push({
            date: date.toISOString().split('T')[0],
            day: date.getDate(),
            isToday: this.isToday(date),
            reservations: 0,
            reservationList: [],
          });
        } else {
          week.push(null);
        }

        if ((i + 1) % 7 === 0) {
          weeks.push(week);
          week = [];
        }
      }

      this.calendar = weeks;
    },
    async loadReservations() {
      try {
        const params = this.spaceId ? `?space_id=${this.spaceId}` : '';
        const response = await axios.get(`/api/reservations${params}`);
        this.reservations = response.data.data || response.data;
        this.updateCalendarWithReservations();
      } catch (error) {
        console.error('Erro ao carregar reservas:', error);
      }
    },
    updateCalendarWithReservations() {
      this.calendar.forEach(week => {
        week.forEach(day => {
          if (day) {
            const dayReservations = this.reservations.filter(r => 
              r.reservation_date === day.date && r.status === 'approved'
            );
            day.reservations = dayReservations.length;
            day.reservationList = dayReservations;
          }
        });
      });
    },
    isToday(date) {
      const today = new Date();
      return date.toDateString() === today.toDateString();
    },
    getDayClass(day) {
      if (!day) return '';
      const classes = ['p-2'];
      if (day.isToday) classes.push('bg-primary', 'bg-opacity-10');
      if (day.reservations > 0) classes.push('border-warning');
      return classes.join(' ');
    },
    selectDay(day) {
      if (day) {
        this.selectedDay = day;
      }
    },
    previousMonth() {
      if (this.currentMonth === 0) {
        this.currentMonth = 11;
        this.currentYear--;
      } else {
        this.currentMonth--;
      }
      this.generateCalendar();
      this.loadReservations();
    },
    nextMonth() {
      if (this.currentMonth === 11) {
        this.currentMonth = 0;
        this.currentYear++;
      } else {
        this.currentMonth++;
      }
      this.generateCalendar();
      this.loadReservations();
    },
  },
};
</script>

<style scoped>
.reservation-calendar td {
  transition: background-color 0.2s;
}

.reservation-calendar td:hover {
  background-color: rgba(102, 126, 234, 0.1);
}
</style>

