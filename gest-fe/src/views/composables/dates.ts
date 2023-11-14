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
    getWeekDates(year: number, week: number): Array<Dayjs>;
    getWeeks(year: number): Array<HoraTreeNode>;
}

export function useDates(): useDates {
    return {
        getWeekNumber: function (d: Date): number {
            return dayjs(d).week();
        },
        getDate(year: number, week: number, weekDay: number) {
            return dayjs().year(year).week(week).weekday(weekDay);
        },
        getWeekDates(year: number, week: number) {
            return [...Array(7).keys()].map((weekDay) => dayjs().year(year).week(week).weekday(weekDay));
        },
        getWeeks(year: number) {
            year = 2024
            let firstDayOfWeek = dayjs().year(year).startOf('year');
            let lastDayOfWeek = firstDayOfWeek.weekday(6);
            const months: Array<HoraTreeNode> = [];

            while(lastDayOfWeek.year() === year) {
                if (undefined === months[firstDayOfWeek.month()]) {
                    months.push ({
                        key: firstDayOfWeek.format('MMM'),
                        label: firstDayOfWeek.format('MMMM'),
                        children: []
                    });
                }
                months[firstDayOfWeek.month()].children.push({
                    key: firstDayOfWeek.format('w'),
                    label: firstDayOfWeek.format(
                        'w (' +
                        firstDayOfWeek.format('D') +
                        '-' +
                        lastDayOfWeek.format('D') +
                        ')'
                    )
                });
                lastDayOfWeek = lastDayOfWeek.add(1, 'week').weekday(6);
                firstDayOfWeek = firstDayOfWeek.add(1, 'week').weekday(0);

            }
            return months;  

        },
    };
}
