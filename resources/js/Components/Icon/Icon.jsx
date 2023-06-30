
export default function Icon({ id, ...props }) {
    return (
        <svg {...props}>
          <use href={`/assets/icons/sprite.svg#${id}`} />
        </svg>
    );
}