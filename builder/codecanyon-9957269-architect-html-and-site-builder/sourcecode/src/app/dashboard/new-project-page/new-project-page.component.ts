import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {ActivatedRoute, Router} from '@angular/router';
import {Settings} from 'common/core/config/settings.service';
import {Modal} from 'common/core/ui/dialogs/modal.service';
import {BuilderProject, BuilderTemplate} from '../../shared/builder-types';
import {PaginationResponse} from '../../../common/core/types/pagination-response';
import {CrupdateProjectModalComponent} from '../../shared/crupdate-project-modal/crupdate-project-modal.component';
import {FormControl, FormGroup} from '@angular/forms';

interface FilterFormValue {
    search: string;
    category: string;
}

@Component({
    selector: 'new-project-page',
    templateUrl: './new-project-page.component.html',
    styleUrls: ['./new-project-page.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class NewProjectPageComponent implements OnInit {
    public allCategories: string[] = [];
    public templatePagination: PaginationResponse<BuilderTemplate>;
    public filteredTemplates: BuilderTemplate[] = [];

    public filterForm = new FormGroup({
        search: new FormControl(),
        category: new FormControl(null),
    });

    constructor(
        private route: ActivatedRoute,
        private settings: Settings,
        private modal: Modal,
        private router: Router,
    ) {}

    ngOnInit() {
        this.allCategories = this.settings.getJson('builder.template_categories', []);

        this.filterForm.valueChanges.subscribe((value: FilterFormValue) => {
            this.applyFilters(value);
        });

        this.route.data.subscribe(resolve => {
            this.templatePagination = resolve.templates;
            this.filteredTemplates = resolve.templates.data;
        });
    }

    public openNewProjectModal(templateName?: string) {
        this.modal.open(CrupdateProjectModalComponent, {templateName})
            .afterClosed().subscribe((project: BuilderProject) => {
                if ( ! project) return;
                this.router.navigate(['/builder', project.model.id]);
            });
    }

    public getTemplateThumbnail(template: BuilderTemplate) {
        return this.settings.getBaseUrl(true) + template.thumbnail;
    }

    public addMoreTemplates(templatePagination: PaginationResponse<BuilderTemplate>) {
        const oldTemplates = this.templatePagination.data.slice();
        this.templatePagination = templatePagination;
        this.templatePagination.data.push(...oldTemplates);
        this.filteredTemplates = this.templatePagination.data;
    }

    public applyFilters(value: FilterFormValue) {
        this.filteredTemplates = this.templatePagination.data.filter(template => {
            const templateName = template.config.name || template.config.display_name || '';
            const matchesCategory = !value.category || template.config.category === value.category;
            const matchesSearch = !value.search || templateName.toLowerCase().indexOf(value.search.toLowerCase()) > -1;
            return matchesCategory && matchesSearch;
        });
    }
}
