import {Injectable} from '@angular/core';
import {Settings} from "common/core/config/settings.service";
import {CurrentUser} from "common/auth/current-user";
import {Project} from './Project';

@Injectable()
export class ProjectUrl {

    /**
     * ProjectBaseUrlService Constructor.
     */
    constructor(private settings: Settings, private currentUser: CurrentUser) {}

    /**
     * Get specified project's base url for the builder.
     */
    public getBaseUrl(project: Project, relative: boolean = false): string {
        const uri = 'projects/'+this.getProjectUserId(project)+'/'+project.uuid+'/';

        if (relative) return uri;

        return this.settings.getBaseUrl()+'storage/' + uri;
    }

    /**
     * Get ID of specified project's creator.
     */
    private getProjectUserId(project: Project): number|string {
        if ( ! project.users || ! project.users.length) {
            return this.currentUser.get('id');
        }

        return project.users[0].id;
    }

    /**
     * Get production site url for specified project.
     */
    public getSiteUrl(project: Project): string {
        let base = this.settings.getBaseUrl(true);
        const protocol = base.match(/(^\w+:|^)\/\//)[0];

        if (this.settings.get('builder.routing_type') === 'subdomain') {
            //strip protocol from the url
            return protocol+project.name+'.'+base.replace(protocol, '');
        }

        return this.settings.getBaseUrl(true)+'sites/'+project.name;
    }
}
