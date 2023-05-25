import type { TreeNode } from 'primevue/tree';

interface HoraTreeNode extends TreeNode {
    key: string,
    label: string,
    children: Array<{key: string, label: string}>
}

interface useDates {
    getWeekNumber(d: Date): number
    weekDays: Array<{label:string, value: number}>,
    weeks: Array<HoraTreeNode>
};

export function useDates(): useDates {
    return {
        getWeekNumber(d: Date): number {
            const onejan = new Date(d.getFullYear(), 0, 1);
            return Math.ceil((((d.getTime() - onejan.getTime()) / 86400000) + onejan.getDay() + 1) / 7);
        },
        weekDays: [
            { label: 'Monday', value: 1 },
            { label: 'Tuesday', value: 2 },
            { label: 'Wednesday', value: 3 },
            { label: 'Thursday', value: 4 },
            { label: 'Friday', value: 5 },
            { label: 'Saturday', value: 6 },
            { label: 'Sunday', value: 0 },
        ],
        weeks: [
            {
                key: 'jan',
                label: 'January',
                children: [
                    { key: '1', label: '1 (1-7)' },
                    { key: '2', label: '2 (8-14)' },
                    { key: '3', label: '3 (15-21)' },
                    { key: '4', label: '4 (22-28)' },
                    { key: '5', label: '5 (29-4)' },
                ],
            },
            {
                key: 'feb',
                label: 'February',
                children: [
                    { key: '6', label: '6 (5-11)' },
                    { key: '7', label: '7 (12-18)' },
                    { key: '8', label: '8 (19-25)' },
                    { key: '9', label: '9 (26-4)' },
                ],
            },
            {
                key: 'mar',
                label: 'March',
                children: [
                    { key: '10', label: '10 (5-11)' },
                    { key: '11', label: '11 (12-18)' },
                    { key: '12', label: '12 (19-25)' },
                    { key: '13', label: '13 (26-1)' },
                ],
            },
            {
                key: 'apr',
                label: 'April',
                children: [
                    { key: '14', label: '14 (2-8)' },
                    { key: '15', label: '15 (9-15)' },
                    { key: '16', label: '16 (16-22)' },
                    { key: '17', label: '17 (23-29)' },
                    { key: '18', label: '17 (30-6)' },
                ],
            },
            {
                key: 'may',
                label: 'May',
                children: [
                    { key: '19', label: '19 (7-13)' },
                    { key: '20', label: '20 (14-20)' },
                    { key: '21', label: '21 (21-27)' },
                    { key: '22', label: '22 (28-3)' },
                ],
            },
            {
                key: 'jun',
                label: 'June',
                children: [
                    { key: '23', label: '23 (4-10)' },
                    { key: '24', label: '24 (11-17)' },
                    { key: '25', label: '25 (18-24)' },
                    { key: '26', label: '26 (25-1)' },
                ],
            },
            {
                key: 'jul',
                label: 'July',
                children: [
                    { key: '27', label: '27 (2-8)' },
                    { key: '28', label: '28 (9-15)' },
                    { key: '29', label: '29 (16-11)' },
                    { key: '30', label: '30 (23-29)' },
                    { key: '31', label: '31 (30-5)' },
                ],
            },
            {
                key: 'aug',
                label: 'August',
                children: [
                    { key: '32', label: '32 (6-12)' },
                    { key: '33', label: '33 (13-19)' },
                    { key: '34', label: '34 (20-26)' },
                    { key: '35', label: '35 (27-2)' },
                ],
            },
            {
                key: 'sep',
                label: 'September',
                children: [
                    { key: '36', label: '36 (3-9)' },
                    { key: '37', label: '37 (10-16)' },
                    { key: '38', label: '38 (17-23)' },
                    { key: '39', label: '39 (24-30)' },
                ],
            },
            {
                key: 'oct',
                label: 'October',
                children: [
                    { key: '40', label: '40 (1-7)' },
                    { key: '41', label: '41 (8-14)' },
                    { key: '42', label: '42 (15-21)' },
                    { key: '43', label: '43 (22-28)' },
                    { key: '44', label: '44 (29-4)' },
                ],
            },
            {
                key: 'nov',
                label: 'November',
                children: [
                    { key: '45', label: '45 (5-11)' },
                    { key: '46', label: '46 (12-18)' },
                    { key: '47', label: '47 (19-25)' },
                    { key: '48', label: '48 (26-2)' },
                ],
            },
            {
                key: 'dec',
                label: 'December',
                children: [
                    { key: '49', label: '49 (3-9)' },
                    { key: '50', label: '50 (10-16)' },
                    { key: '51', label: '51 (17-23)' },
                    { key: '52', label: '52 (24-30)' },
                    { key: '53', label: '53 (31)' },
                ],
            }
        ]
    }
}