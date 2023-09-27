import {useShareableLinkPage} from '../../queries/use-shareable-link-page';
import {useNavigate} from 'react-router-dom';
import {ShareableLink} from '../../shareable-link';

function buildFolderHash(link: ShareableLink, folderHash?: string) {
  let hash = link.hash;
  if (folderHash && link.entry?.hash !== folderHash) {
    hash = `${hash}:${folderHash}`;
  }
  return hash;
}

export function useNavigateToSubfolder() {
  const {link} = useShareableLinkPage();
  const navigate = useNavigate();
  return (hash: string) => {
    if (!link) return;
    navigate(`/drive/s/${buildFolderHash(link, hash)}`);
  };
}
