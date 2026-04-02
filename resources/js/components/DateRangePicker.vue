<template>
    <div class="date-range-picker">
        <div class="calendar-container">
            <div class="calendar-header">
                <button @click="previousMonth" class="nav-button">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <h3 class="month-year">{{ currentMonthYear }}</h3>
                <button @click="nextMonth" class="nav-button">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            <div class="calendar-grid">
                <div class="day-header" v-for="day in dayHeaders" :key="day">
                    {{ day }}
                </div>

                <div
                    v-for="day in calendarDays"
                    :key="day.date"
                    class="calendar-day"
                    :class="getDayClasses(day)"
                    @click="selectDate(day)"
                    @mouseenter="hoverDate(day)"
                    @mouseleave="clearHover"
                >
                    {{ day.day }}
                </div>
            </div>

            <div
                class="selected-range"
                v-if="selectedRange.start || selectedRange.end"
            >
                <div class="range-info">
                    <span v-if="selectedRange.start">
                        Desde: {{ formatDate(selectedRange.start) }}
                    </span>
                    <span v-if="selectedRange.end">
                        Hasta: {{ formatDate(selectedRange.end) }}
                    </span>
                </div>
            </div>

            <div class="calendar-actions">
                <button @click="clearSelection" class="clear-btn">
                    Limpiar
                </button>
                <button
                    @click="applySelection"
                    class="apply-btn"
                    :disabled="!isRangeComplete"
                >
                    Aplicar
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted } from "vue";

export default {
    name: "DateRangePicker",
    emits: ["range-selected"],
    setup(props, { emit }) {
        const currentDate = ref(new Date());
        const selectedRange = ref({ start: null, end: null });
        const hoveredDate = ref(null);
        const isSelecting = ref(false);

        const dayHeaders = ["L", "M", "X", "J", "V", "S", "D"];

        const currentMonthYear = computed(() => {
            return currentDate.value.toLocaleDateString("es-ES", {
                month: "long",
                year: "numeric",
            });
        });

        const calendarDays = computed(() => {
            const year = currentDate.value.getFullYear();
            const month = currentDate.value.getMonth();

            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const startDate = new Date(firstDay);
            startDate.setDate(startDate.getDate() - firstDay.getDay() + 1);

            const days = [];
            const current = new Date(startDate);

            for (let i = 0; i < 42; i++) {
                days.push({
                    date: new Date(current),
                    day: current.getDate(),
                    isCurrentMonth: current.getMonth() === month,
                    isToday: isSameDay(current, new Date()),
                    isPast: current < new Date(new Date().setHours(0, 0, 0, 0)),
                });
                current.setDate(current.getDate() + 1);
            }

            return days;
        });

        const isRangeComplete = computed(() => {
            return selectedRange.value.start && selectedRange.value.end;
        });

        const isSameDay = (date1, date2) => {
            return (
                date1.getDate() === date2.getDate() &&
                date1.getMonth() === date2.getMonth() &&
                date1.getFullYear() === date2.getFullYear()
            );
        };

        const getDayClasses = (day) => {
            const classes = [];

            if (!day.isCurrentMonth) classes.push("other-month");
            if (day.isToday) classes.push("today");
            if (day.isPast) classes.push("past");

            if (
                selectedRange.value.start &&
                isSameDay(day.date, selectedRange.value.start)
            ) {
                classes.push("selected-start");
            }

            if (
                selectedRange.value.end &&
                isSameDay(day.date, selectedRange.value.end)
            ) {
                classes.push("selected-end");
            }

            if (isInRange(day.date)) {
                classes.push("in-range");
            }

            if (hoveredDate.value && isSameDay(day.date, hoveredDate.value)) {
                classes.push("hovered");
            }

            return classes;
        };

        const isInRange = (date) => {
            if (!selectedRange.value.start || !selectedRange.value.end)
                return false;

            const start = selectedRange.value.start;
            const end = selectedRange.value.end;

            return date >= start && date <= end;
        };

        const selectDate = (day) => {
            if (day.isPast || !day.isCurrentMonth) return;

            if (!selectedRange.value.start || selectedRange.value.end) {
                // Iniciar nueva selección
                selectedRange.value = { start: day.date, end: null };
                isSelecting.value = true;
            } else {
                // Completar selección
                if (day.date >= selectedRange.value.start) {
                    selectedRange.value.end = day.date;
                } else {
                    selectedRange.value = {
                        start: day.date,
                        end: selectedRange.value.start,
                    };
                }
                isSelecting.value = false;
            }
        };

        const hoverDate = (day) => {
            if (!isSelecting.value || !selectedRange.value.start) return;
            hoveredDate.value = day.date;
        };

        const clearHover = () => {
            hoveredDate.value = null;
        };

        const previousMonth = () => {
            currentDate.value = new Date(
                currentDate.value.getFullYear(),
                currentDate.value.getMonth() - 1,
            );
        };

        const nextMonth = () => {
            currentDate.value = new Date(
                currentDate.value.getFullYear(),
                currentDate.value.getMonth() + 1,
            );
        };

        const clearSelection = () => {
            selectedRange.value = { start: null, end: null };
            isSelecting.value = false;
            hoveredDate.value = null;
        };

        const applySelection = () => {
            if (isRangeComplete.value) {
                emit("range-selected", {
                    startDate: formatDateForAPI(selectedRange.value.start),
                    endDate: formatDateForAPI(selectedRange.value.end),
                });
            }
        };

        const formatDate = (date) => {
            return date.toLocaleDateString("es-ES");
        };

        const formatDateForAPI = (date) => {
            return date.toISOString().split("T")[0];
        };

        return {
            currentDate,
            selectedRange,
            hoveredDate,
            isSelecting,
            dayHeaders,
            currentMonthYear,
            calendarDays,
            isRangeComplete,
            getDayClasses,
            selectDate,
            hoverDate,
            clearHover,
            previousMonth,
            nextMonth,
            clearSelection,
            applySelection,
            formatDate,
        };
    },
};
</script>

<style scoped>
.date-range-picker {
    background: white;
    border-radius: 12px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    padding: 24px;
    max-width: 400px;
    margin: 0 auto;
}

.calendar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}

.nav-button {
    background: #f8fafc;
    border: none;
    border-radius: 8px;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #64748b;
}

.nav-button:hover {
    background: #e2e8f0;
    color: #334155;
}

.month-year {
    font-size: 18px;
    font-weight: 600;
    color: #1e293b;
    text-transform: capitalize;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
    margin-bottom: 20px;
}

.day-header {
    text-align: center;
    font-weight: 600;
    color: #64748b;
    font-size: 12px;
    padding: 8px 0;
}

.calendar-day {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 14px;
    font-weight: 500;
    position: relative;
}

.calendar-day.other-month {
    color: #cbd5e1;
    cursor: not-allowed;
}

.calendar-day.past {
    color: #cbd5e1;
    cursor: not-allowed;
}

.calendar-day.today {
    background: #dbeafe;
    color: #1d4ed8;
    font-weight: 600;
}

.calendar-day:hover:not(.other-month):not(.past) {
    background: #f1f5f9;
    color: #1e293b;
}

.calendar-day.selected-start {
    background: #3b82f6;
    color: white;
    font-weight: 600;
}

.calendar-day.selected-end {
    background: #3b82f6;
    color: white;
    font-weight: 600;
}

.calendar-day.in-range {
    background: #dbeafe;
    color: #1d4ed8;
}

.calendar-day.hovered {
    background: #e0f2fe;
    color: #0369a1;
}

.selected-range {
    background: #f8fafc;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 20px;
}

.range-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 14px;
    color: #475569;
}

.calendar-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.clear-btn,
.apply-btn {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
}

.clear-btn {
    background: #f1f5f9;
    color: #64748b;
}

.clear-btn:hover {
    background: #e2e8f0;
    color: #475569;
}

.apply-btn {
    background: #3b82f6;
    color: white;
}

.apply-btn:hover:not(:disabled) {
    background: #2563eb;
}

.apply-btn:disabled {
    background: #cbd5e1;
    cursor: not-allowed;
}
</style>
