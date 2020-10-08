import {User} from 'common/core/types/models/User';

export class Project {
    id: number;
    name: string;
    published = 1;
    public = 0;
    uuid?: string;
    framework = '';
    theme = '';
    template = '';
    users?: User[];
    created_at?: string;

    constructor(params: Object = {}) {
        for (const name in params) {
            this[name] = params[name];
        }
    }
}