import {Injectable} from '@angular/core';
import {Resolve, RouterStateSnapshot, ActivatedRouteSnapshot, Router} from '@angular/router';
import {CurrentUser} from 'common/auth/current-user';
import {PaginationResponse} from 'common/core/types/pagination-response';
import {Project} from '../shared/projects/Project';
import {Projects} from '../shared/projects/projects.service';
import {map} from 'rxjs/operators';

@Injectable()
export class ProjectsResolver implements Resolve<PaginationResponse<Project>> {

    constructor(
        private router: Router,
        private projects: Projects,
        private currentUser: CurrentUser,
    ) {}

    resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Promise<PaginationResponse<Project>> {
        return this.projects.all({user_id: this.currentUser.get('id'), per_page: 20})
            .pipe(map(response => response.pagination))
            .toPromise();
    }
}