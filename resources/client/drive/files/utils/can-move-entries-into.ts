import {FileEntry} from '@common/uploads/file-entry';

export interface PartialFolder {
  id: number;
  name: string;
  path: string;
  parent_id: number | null;
  type: string;
}

export function canMoveEntriesInto(
  targets: FileEntry[],
  destination: PartialFolder
) {
  if (destination.type !== 'folder') return false;

  // should not be able to move folder into its
  // own child or folder it's already in
  return targets.every(target => {
    if (!target) return false;
    // entry is already in this folder
    if (
      destination.id === target.parent_id ||
      // root folder check
      (!target.parent_id && destination.id === 0)
    ) {
      return false;
    }

    return !destinationIsInTarget(destination, target);
  });
}

function destinationIsInTarget(destination: PartialFolder, target: FileEntry) {
  const destinationPath = (destination.path || '').split('/');
  const targetPath = (target.path || '').split('/');
  return targetPath.every((part, index) => {
    return destinationPath[index] === part;
  });
}
