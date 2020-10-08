import {Injectable} from '@angular/core';
import {Resolve, RouterStateSnapshot, ActivatedRouteSnapshot, Router} from '@angular/router';
import {catchError, map, mergeMap} from 'rxjs/operators';
import { Plan } from '../../shared/billing/models/plan';
import { Plans } from '../../shared/billing/plans.service';
import {EMPTY, Observable, of} from 'rxjs';

@Injectable()
export class BillingPlansResolver implements Resolve<Plan[]> {
    constructor(
        private plans: Plans,
        private router: Router
    ) {}

    resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Observable<Plan[]> {
        return this.plans.all({order: 'position|asc'})
            .pipe(map(response => response.pagination.data))
            .pipe(
                catchError(() => {
                    this.router.navigateByUrl('/');
                    return EMPTY;
                }),
                mergeMap((plans: Plan[]) => {
                    return of(plans);
                })
            );
    }
}

