import {FileEntry} from '@common/uploads/file-entry';

export function makeFolderTreeDragId(entry: FileEntry) {
  return `${entry.id}-tree`;
}

export function isFolderTreeDragId(id: string | number): boolean {
  return `${id}`.endsWith('-tree');
}
