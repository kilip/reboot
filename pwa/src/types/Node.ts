import { type Item } from './item';

export class Node implements Item {
  public '@id'?: string;

  constructor(
    public hostname: string,
    public ipAddress: string,
    public macAddress: string,
    public online: boolean,
    public uptime: object,
    public draft: boolean,
    _id?: string
  ) {
    this[ '@id' ] = _id;
  }
}
