import {Injectable} from '@angular/core';
import {HttpCacheClient} from '../http/http-cache-client';
import {Page} from '../types/models/Page';
import {BackendResponse} from '../types/backend-response';
import {PaginatedBackendResponse} from '../types/paginated-backend-response';

@Injectable({
    providedIn: 'root',
})
export class Pages {

    /**
     * Base uri for pages api.
     */
    private baseUri = 'pages';

    /**
     * TagsService Constructor.
     */
    constructor(private http: HttpCacheClient) {}

    /**
     * Fetch all existing pages from the server.
     */
    public getAll(params = {}): PaginatedBackendResponse<Page> {
        return this.http.getWithCache(this.baseUri, params);
    }

    /**
     * Fetch a page matching specified id.
     */
    public get(id: number|string): BackendResponse<{page: Page}> {
        return this.http.getWithCache(this.baseUri + '/' + id);
    }

    /**
     * Create a new page.
     */
    public create(data: Object) {
        return this.http.post(this.baseUri, data);
    }

    /**
     * Update existing page.
     */
    public update(id: number, data: Object) {
        return this.http.put(this.baseUri + '/' + id, data);
    }

    /**
     * Delete specified pages.
     */
    public delete(ids: number[]) {
        return this.http.delete(this.baseUri, {ids});
    }
}
