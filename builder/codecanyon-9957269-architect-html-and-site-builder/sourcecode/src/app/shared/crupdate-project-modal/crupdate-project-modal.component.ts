import {Component, Inject, OnInit, ViewEncapsulation} from '@angular/core';
import {MAT_DIALOG_DATA, MatDialogRef} from '@angular/material';
import {Toast} from 'common/core/ui/toast.service';
import {Themes} from '../themes/themes.service';
import {Project} from '../projects/Project';
import {Projects} from '../projects/projects.service';
import {BuilderTemplate} from '../builder-types';
import {PageDocument} from '../page-document';
import {Templates} from '../templates/templates.service';
import {ProjectUrl} from '../projects/project-url.service';
import {forkJoin} from 'rxjs';
import {Theme} from '../themes/Theme';
import {randomString} from '../../../common/core/utils/random-string';

const DEFAULT_FRAMEWORK = 'bootstrap-3';

interface CrupdateProjectModalData {
    project?: Project;
    templateName?: string;
    showExtraConfig?: boolean;
}

@Component({
    selector: 'crupdate-project-modal',
    templateUrl: './crupdate-project-modal.component.html',
    styleUrls: ['./crupdate-project-modal.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class CrupdateProjectModalComponent implements OnInit {
    public themes: Theme[] = [];
    public templates: BuilderTemplate[] = [];
    public loading = false;
    public model: Project;
    public updating = false;
    public errors: any = {};
    private pageDocument = new PageDocument();

    constructor(
        private dialogRef: MatDialogRef<CrupdateProjectModalComponent>,
        @Inject(MAT_DIALOG_DATA) public data: CrupdateProjectModalData,
        public projects: Projects,
        private toast: Toast,
        private themesApi: Themes,
        private templatesApi: Templates,
        private projectUrl: ProjectUrl,
    ) {}

    ngOnInit() {
        if (this.data.showExtraConfig) {
            this.getThemesAndTemplates();
        }

        this.hydrateModel();
        this.pageDocument.setBaseUrl(this.projectUrl.getBaseUrl(this.model));
        this.updating = !!this.data.project;
    }

    public async confirm() {
        this.loading = true;
        let request;

        if (this.updating) {
            request = this.projects.update(this.data.project.id, this.model);
        } else {
            request = this.projects.create(await this.getNewProjectPayload());
        }

        request.subscribe(response => {
            this.close(response.project);
            const action = this.updating ? 'updated' : 'created';
            this.toast.open('Project has been ' + action);
            this.loading = false;
        }, response => {
            this.errors = response.messages;
            this.loading = false;
        });
    }

    public close(data?: any) {
        this.dialogRef.close(data);
    }

    private hydrateModel() {
        this.errors = {};

        this.model = new Project({
            pages: [],
            template: this.data.templateName || null,
            framework: !this.data.templateName ? DEFAULT_FRAMEWORK : null,
            uuid: randomString(36)
        });

        if (this.data.project) {
            this.model.name = this.data.project.name;
            this.model.framework = this.data.project.framework;
            this.model.theme = this.data.project.theme;
        }
    }

    private getThemesAndTemplates() {
        forkJoin(
            this.themesApi.all(),
            this.templatesApi.all(),
        ).subscribe(response => {
            this.themes = response[0].themes;
            this.templates = response[1].pagination.data;
        });
    }


    private getNewProjectPayload() {
        const templateName = this.data.templateName || this.model.template;
        if (templateName) {
            return this.createProjectFromTemplate(templateName);
        } else {
            return this.createBlankProject();
        }
    }

    private createProjectFromTemplate(templateName: string): Promise<any> {
        return new Promise(resolve => {
            const params = this.model as any;

            this.templatesApi.get(templateName).subscribe(response => {
                params.template = response.template;
                params.framework = response.template.config.framework;
                params.pages = this.transformTemplatePages(response.template);
                resolve(params);
            });
        });
    }

    private createBlankProject() {
        const params = this.model as any;

        params.pages.push({
            name: 'index',
            html: this.pageDocument.generate().getOuterHtml()
        });

        return params;
    }

    private transformTemplatePages(template: BuilderTemplate) {
        return template.pages.map(page => {
            return {
                name: page.name,
                html: this.pageDocument.generate(page.html, template).getOuterHtml(),
            };
        });
    }
}
