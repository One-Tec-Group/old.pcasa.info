import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {ActivatedRoute, Router} from "@angular/router";
import {CurrentUser} from "common/auth/current-user";
import {Toast} from "common/core/ui/toast.service";
import {ConfirmModalComponent} from "common/core/ui/confirm-modal/confirm-modal.component";
import {FormControl, FormGroup} from "@angular/forms";
import {UrlAwarePaginator} from "common/admin/pagination/url-aware-paginator.service";
import {Project} from '../shared/projects/Project';
import {Projects} from '../shared/projects/projects.service';
import {ProjectUrl} from '../shared/projects/project-url.service';
import {PublishProjectModalComponent} from '../shared/projects/publish-project-modal/publish-project-modal.component';
import {Settings} from '../../common/core/config/settings.service';
import {Modal} from '../../common/core/ui/dialogs/modal.service';
import {debounceTime, distinctUntilChanged} from 'rxjs/operators';
import {randomString} from '../../common/core/utils/random-string';

declare interface ProjectFilters {
    order: string,
    status: string,
    query: string;
}

@Component({
    selector: 'dashboard',
    templateUrl: './dashboard.component.html',
    styleUrls: ['./dashboard.component.scss'],
    providers: [UrlAwarePaginator],
    encapsulation: ViewEncapsulation.None
})
export class DashboardComponent implements OnInit {
    public loading = false;
    public projects: Project[] = [];

    public models = new FormGroup({
        query:  new FormControl(''),
        order: new FormControl('created_at|desc'),
        published: new FormControl('all')
    });

    constructor(
        private route: ActivatedRoute,
        private router: Router,
        public settings: Settings,
        public currentUser: CurrentUser,
        private projectsApi: Projects,
        private toast: Toast,
        private modal: Modal,
        private projectUrl: ProjectUrl,
        private paginator: UrlAwarePaginator,
    ) {}

    ngOnInit() {
        this.route.data.subscribe(data => {
            this.projects = data.projects.data;
        });

        this.bindToProjectFilters();
    }

    public openBuilder(project: Project) {
        this.loading = true;
        this.router.navigate(['/builder', project.id]).then(() => {
            this.loading = false;
        });
    }

    public getProjectImage(project: Project) {
        return this.projectUrl.getBaseUrl(project) + 'thumbnail.png';
    }

    public getProjectUrl(project: Project) {
        return this.projectUrl.getSiteUrl(project);
    }

    public openPublishProjectModal(project: Project) {
        this.modal.open(PublishProjectModalComponent, {project}).afterClosed().subscribe(project => {
            if ( ! project || ! project.model) return;
            const i = this.projects.findIndex(curr => curr.id === project.model.id);
            this.projects[i] = project.model;
        });
    }

    public deleteProjectWithConfirmation(project: Project) {
        this.modal.open(ConfirmModalComponent, {
            title: 'Delete Project',
            body: 'Are you sure you want to delete this project?',
            ok: 'Delete',
        }).afterClosed().subscribe(confirmed => {
            if ( ! confirmed) return;

            this.projectsApi.delete({ids: [project.id]}).subscribe(() => {
                this.toast.open('Project deleted');
                this.projects.splice(this.projects.indexOf(project), 1);
            });
        });
    }

    private bindToProjectFilters() {
        this.models.valueChanges.pipe(debounceTime(250), distinctUntilChanged())
            .subscribe((params: ProjectFilters) => {
                const merged = Object.assign({user_id: this.currentUser.get('id'), per_page: 20}, params);

                this.paginator.paginate('projects', merged).subscribe(response => {
                    this.projects = response.data;
                });
            });
    }
}
