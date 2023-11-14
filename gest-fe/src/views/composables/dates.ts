import type { Dayjs } from 'dayjs';
import dayjs from 'dayjs';
import type { TreeNode } from 'primevue/tree';

interface HoraTreeNode extends TreeNode {
    key: string;
    label: string;
    children: Array<{ key: string; label: string }>;
}

interface useDates {
    getWeekNumber(d: Date): number;
    getDate(year: number, week: number, weekDay: number): Dayjs;
    getWeekDay(date: Date): number;
    getWeekDates(year: number, week: number): Array<Dayjs>;
    getWeeks(year: number): Array<HoraTreeNode>;
}

export function useDates(): useDates {
    const getWeekDay = (date: Date): number => {
        return dayjs(date).weekday();
    }
    const getDate = function (year: number, week: number, weekDay: number) {
        return dayjs().year(year).week(week).weekday(weekDay);
    }
    return {
        getWeekNumber: function (d: Date): number {
            return dayjs(d).week();
        },
        getWeekDay,
        getDate,
        getWeekDates(year: number, week: number) {
            const weekDates: Array<Dayjs> = [];
            for (let i = 1; i <= 7; i++) {
                weekDates.push(getDate(year, week, i));
            }
            return weekDates;
        },
        getWeeks(year: number) {
            let firstDayOfWeek = dayjs().year(year).startOf('year');
            let lastDayOfWeek = firstDayOfWeek.day() === 0 ? firstDayOfWeek : firstDayOfWeek.add(7 - firstDayOfWeek.day(), 'day');
            const months: Array<HoraTreeNode> = [];

            while(lastDayOfWeek.year() === year) {
                if (undefined === months[lastDayOfWeek.month()]) {
                    months.push ({
                        key: lastDayOfWeek.format('MMM'),
                        label: lastDayOfWeek.format('MMMM'),
                        children: []
                    });
                }
                months[lastDayOfWeek.month()].children.push({
                    key: lastDayOfWeek.format('w'),
                    label: lastDayOfWeek.format(
                        'w (' +
                        firstDayOfWeek.format('D') +
                        '-' +
                        lastDayOfWeek.format('D') +
                        ')'
                    )
                });
                lastDayOfWeek = lastDayOfWeek.add(1, 'week');
                firstDayOfWeek = firstDayOfWeek.add(1, 'week');

            }
            return months;  

        },
    };
}
