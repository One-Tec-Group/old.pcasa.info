import {Component, OnInit, ViewChild, ViewEncapsulation} from '@angular/core';
import {UrlAwarePaginator} from "common/admin/pagination/url-aware-paginator.service";
import {Modal} from "common/core/ui/dialogs/modal.service";
import {CurrentUser} from "common/auth/current-user";
import {ConfirmModalComponent} from "common/core/ui/confirm-modal/confirm-modal.component";
import {MatSort} from "@angular/material";
import {Projects} from '../../shared/projects/projects.service';
import {Project} from '../../shared/projects/Project';
import {ProjectUrl} from '../../shared/projects/project-url.service';
import {PaginatedDataTableSource} from '../../../common/admin/data-table/data/paginated-data-table-source';
import {CrupdateProjectModalComponent} from '../../shared/crupdate-project-modal/crupdate-project-modal.component';

@Component({
    selector: 'projects',
    templateUrl: './projects.component.html',
    styleUrls: ['./projects.component.scss'],
    providers: [UrlAwarePaginator],
    encapsulation: ViewEncapsulation.None
})
export class ProjectsComponent implements OnInit {
    @ViewChild(MatSort) matSort: MatSort;

    public dataSource: PaginatedDataTableSource<Project>;

    constructor(
        public paginator: UrlAwarePaginator,
        private projects: Projects,
        private modal: Modal,
        private projectUrl: ProjectUrl,
        public currentUser: CurrentUser,
    ) {}

    ngOnInit() {
        this.dataSource = new PaginatedDataTableSource<Project>({
            uri: 'projects',
            dataPaginator: this.paginator,
            matSort: this.matSort
        });
    }

    /**
     * Ask user to confirm deletion of selected projects
     * and delete selected projects if user confirms.
     */
    public maybeDeleteSelectedProjects() {
        this.modal.show(ConfirmModalComponent, {
            title: 'Delete Projects',
            body:  'Are you sure you want to delete selected projects?',
            ok:    'Delete'
        }).afterClosed().subscribe(confirmed => {
            if ( ! confirmed) return;
            this.deleteSelectedProjects();
        });
    }

    /**
     * Delete currently selected projects.
     */
    public deleteSelectedProjects() {
        const ids = this.dataSource.selectedRows.selected.map(project => project.id);

        this.projects.delete({ids}).subscribe(() => {
            this.paginator.refresh();
            this.dataSource.selectedRows.clear();
        });
    }

    /**
     * Show modal for editing project if project is specified
     * or for creating a new project otherwise.
     */
    public showCrupdateProjectModal(project?: Project) {
        this.modal.show(CrupdateProjectModalComponent, {project, showExtraConfig: true})
            .afterClosed().subscribe(data => {
                if ( ! data) return;
                this.paginator.refresh();
            });
    }

    /**
     * Get relative url for specified project's thumbnail.
     */
    public getProjectThumbnail(project: Project) {
        return this.projectUrl.getBaseUrl(project)+'thumbnail.png';
    }
}
